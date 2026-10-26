// chart
  // Nepal Standard Time is a fixed UTC+5:45 offset (no DST), so we compute
  // session boundaries as explicit UTC instants instead of relying on
  // Date.setHours(), which uses whatever timezone the browser/server is in.
  const NPT_OFFSET_MIN = 5 * 60 + 45; // 345 minutes
  

  function nepalDateParts(ms) {
    const fmt = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'Asia/Kathmandu',
      year: 'numeric', month: '2-digit', day: '2-digit'
    });
    const [y, m, d] = fmt.format(new Date(ms)).split('-').map(Number);
    return { y, m, d };
  }

  function nepalTimeToUtcMs(refMs, hour, minute) {
    const { y, m, d } = nepalDateParts(refMs);
    const utcMinutes = hour * 60 + minute - NPT_OFFSET_MIN;
    return Date.UTC(y, m - 1, d, 0, 0, 0) + utcMinutes * 60 * 1000;
  }

  async function loadNepseChart() {
    try {
      if (typeof ApexCharts === "undefined") {
        console.error("[chart] ApexCharts library did not load — check the CDN <script> tag / network tab.");
        document.querySelector("#chart").innerHTML =
          "<p style='color:#f0475e; padding:20px;'>Chart library failed to load.</p>";
        return;
      }

      const [statusRes, dataRes] = await Promise.all([
        fetch("http://localhost:8000/IsNepseOpen"),
        fetch("http://localhost:8000/DailyNepseIndexGraph")
      ]);

      const status = await statusRes.json();
      const rawData = await dataRes.json();

      if (!rawData || !rawData.length) {
        document.querySelector("#chart").innerHTML =
          "<p style='color:#64748b; padding:20px;'>No chart data available right now.</p>";
        return;
      }

      // Detect whether timestamps are in seconds or milliseconds.
      const sampleTs = rawData[0][0];
      const msPerUnit = sampleTs < 3e10 ? 1000 : 1;

      // This is a single index reading per minute, not real trade OHLC —
      // so a plain line/area series looks far better than forcing it into
      // candlesticks (which just produced flat, zero-body dojis).
      const seriesData = rawData.map(row => ({
        x: row[0] * msPerUnit,
        y: Number(row[1])
      }));

      const firstValue = seriesData[0].y;
      const lastValue = seriesData[seriesData.length - 1].y;
      const change = lastValue - firstValue;
      const changePct = firstValue !== 0 ? (change / firstValue) * 100 : 0;
      const isUp = change >= 0;

      const lineColor = isUp ? "#2fd06c" : "#f0475e";
      const fillColorTop = isUp ? "rgba(47, 208, 108, 0.35)" : "rgba(240, 71, 94, 0.35)";
      const fillColorBottom = isUp ? "rgba(47, 208, 108, 0)" : "rgba(240, 71, 94, 0)";

      // ---- Session window, computed in real Nepal time (UTC+5:45) ----
      const firstMs = seriesData[0].x;
      const sessionStart = nepalTimeToUtcMs(firstMs, 11, 0);
      const sessionEndFull = nepalTimeToUtcMs(firstMs, 15, 0);

      const isOpen = status.isOpen === "OPEN";
      const lastMs = seriesData[seriesData.length - 1].x;
      const sessionEnd = isOpen ? Math.max(lastMs, sessionStart) : sessionEndFull;

      const tickPositions = [];
      const step = 30 * 60 * 1000; // 30 minutes
      for (let t = sessionStart; t <= sessionEnd; t += step) {
        tickPositions.push(t);
      }

      const sessionDateLabel = new Date(firstMs).toLocaleDateString("en-US", {
        timeZone: "Asia/Kathmandu", month: "short", day: "numeric", year: "numeric"
      });

      const options = {
        chart: {
          type: 'area',
          height: 420,
          foreColor: '#64748b',
          fontFamily: 'Inter, sans-serif',
          background: 'transparent',
          toolbar: { show: false },
          zoom: { enabled: false },
          animations: { enabled: true, easing: 'easeinout', speed: 500 }
        },
        colors: [lineColor],
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0,
            stops: [0, 100],
            colorStops: [
              { offset: 0, color: lineColor, opacity: 0.35 },
              { offset: 100, color: lineColor, opacity: 0 }
            ]
          }
        },
        grid: {
          borderColor: '#e2e8f0',
          strokeDashArray: 4,
          xaxis: { lines: { show: false } },
          yaxis: { lines: { show: true } },
          padding: { left: 8, right: 16 }
        },
        markers: { size: 0, hover: { size: 4 } },
        title: {
          text: `NEPSE Index — ${sessionDateLabel}${isOpen ? " · Live" : ""}`,
          align: 'left',
          style: { color: '#1e293b', fontSize: '15px', fontWeight: 700 }
        },
        subtitle: {
          text: `${lastValue.toFixed(2)}  ${isUp ? "▲" : "▼"} ${Math.abs(change).toFixed(2)} (${changePct.toFixed(2)}%)`,
          align: 'left',
          style: { color: lineColor, fontSize: '13px', fontWeight: 600 }
        },
        xaxis: {
          type: 'datetime',
          min: sessionStart,
          max: sessionEnd,
          tickPositions: tickPositions,
          axisBorder: { color: '#e2e8f0' },
          axisTicks: { color: '#e2e8f0' },
          labels: {
            style: { colors: '#64748b', fontSize: '11px' },
            formatter: function (val, timestamp) {
              return new Date(timestamp).toLocaleTimeString("en-US", {
                timeZone: "Asia/Kathmandu",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false
              });
            }
          }
        },
        yaxis: {
          labels: {
            style: { colors: '#64748b', fontSize: '11px' },
            formatter: (val) => val.toFixed(0)
          }
        },
        tooltip: {
          theme: 'light',
          x: {
            formatter: function (timestamp) {
              return new Date(timestamp).toLocaleTimeString("en-US", {
                timeZone: "Asia/Kathmandu",
                hour: "2-digit", minute: "2-digit", hour12: false
              });
            }
          },
          y: { formatter: (val) => val.toFixed(2) }
        },
        dataLabels: { enabled: false },
        series: [{ name: 'NEPSE Index', data: seriesData }]
      };

      new ApexCharts(document.querySelector("#chart"), options).render();
    } catch (error) {
      console.error("Error loading NEPSE chart:", error);
      document.querySelector("#chart").innerHTML =
        "<p style='color:#ef4444; padding:20px;'>Unable to load chart data.</p>";
    }
  }
// top traded scrips

  loadNepseChart();
  fetch("http://localhost:8000/TopTenTradeScrips")
  .then (res=> res.json())
  .then(data=>{
    const tbody = document.querySelector("#traded tbody");
    tbody.innerHTML="";
    data.slice(0,5).forEach(item=>{
      const row=document.createElement("tr");
      row.innerHTML= `
      <td>${item.symbol}</td>
      <td>${item.securityName}</td>
      <td>${item.shareTraded.toLocaleString()}</td>
      <td>${item.closingPrice}</td>

      `;
      tbody.appendChild(row);
    });
  })
  .catch(err => console.error("Error fetching Data:", err));

  // Top Gainers
  fetch("http://localhost:8000/TopGainers")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#gainersTable tbody");
      tbody.innerHTML = "";
      data.slice(0,5).forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${item.symbol}</td>
          <td>${item.securityName}</td>
          
          <td>${item.ltp}</td>  
          <td class="positive">${item.pointChange}</td>
          <td class="positive">+${item.percentageChange}%</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(err => console.error("Error fetching Top Gainers:", err));

  // Top Losers
  fetch("http://localhost:8000/TopLosers")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#losersTable tbody");
      tbody.innerHTML = "";
      data.slice(0,5).forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${item.symbol}</td>
          <td>${item.securityName}</td>
          <td>${item.ltp}</td>
          <td class="negative">${item.pointChange}</td>
          <td class="negative">${item.percentageChange}%</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(err => console.error("Error fetching Top Losers:", err));


    // after editing
     // Fetch data from  API
  fetch("http://localhost:8000/NepseIndex")
  .then(response => response.json())
  .then(data => {
    console.log("API Response:", data);

    const indexCards = document.querySelectorAll("#main-index .index-card");

    const sensitive = data["Sensitive Index"];
    const sensitiveClass = sensitive.change >= 0 ? "positive" : "negative";
    indexCards[1].querySelector(".index-value").innerHTML =
      `<span class="${sensitiveClass}">${sensitive.currentValue}</span><br>
       Change: ${sensitive.change} (${sensitive.perChange}%)`;

    const nepse = data["NEPSE Index"];
    const nepseClass = nepse.change >= 0 ? "positive" : "negative";
    indexCards[0].querySelector(".index-value").innerHTML =
      `<span class="${nepseClass}">${nepse.currentValue}</span><br>
       Change: ${nepse.change} (${nepse.perChange}%)`;

  })
  .catch(error => console.error("Error fetching NEPSE data:", error));



      
    // commit number 2

  
fetch("http://localhost:8000/NepseSubIndices")
  .then(response => response.json())
  .then(data => {
    console.log("Sector API Response:", data);

    const liveMarketCards = document.querySelectorAll("#sub-index .index-card");

    // Bank Index
    const bank = data["Banking SubIndex"];
    if (bank) {
      const bankClass = bank.change >= 0 ? "positive" : "negative";
      liveMarketCards[0].querySelector(".index-value").innerHTML =
        `<span class="${bankClass}">${bank.currentValue}</span><br>
         Change: ${bank.change} (${bank.perChange}%)`;
    }

    // Hydro Index
    const hydro = data["HydroPower Index"];
    if (hydro) {
      const hydroClass = hydro.change >= 0 ? "positive" : "negative";
      liveMarketCards[1].querySelector(".index-value").innerHTML =
        `<span class="${hydroClass}">${hydro.currentValue}</span><br>
         Change: ${hydro.change} (${hydro.perChange}%)`;
    }

    // Finance Index
    const finance = data["Finance Index"];
    if (finance) {
      const financeClass = finance.change >= 0 ? "positive" : "negative";
      liveMarketCards[2].querySelector(".index-value").innerHTML =
        `<span class="${financeClass}">${finance.currentValue}</span><br>
         Change: ${finance.change} (${finance.perChange}%)`;
    }

    // Hotel Index
    const hotel = data["Hotels And Tourism Index"];
    if (hotel) {
      const hotelClass = hotel.change >= 0 ? "positive" : "negative";
      liveMarketCards[3].querySelector(".index-value").innerHTML =
        `<span class="${hotelClass}">${hotel.currentValue}</span><br>
         Change: ${hotel.change} (${hotel.perChange}%)`;
    }
  })
  .catch(error => console.error("Error fetching sector indices:", error));

  //commit for market summary
 
fetch("http://localhost:8000/Summary")
  .then(res => res.json())
  .then(summary => {
    console.log("Market Summary:", summary);

    const summaryCards = document.querySelectorAll("#market-summary .index-card");

    summaryCards[0].querySelector(".index-value").textContent =
      summary["Total Turnover Rs:"].toLocaleString();
    summaryCards[1].querySelector(".index-value").textContent =
      summary["Total Traded Shares"].toLocaleString();
    summaryCards[2].querySelector(".index-value").textContent =
      summary["Total Transactions"].toLocaleString();
    summaryCards[3].querySelector(".index-value").textContent =
      summary["Total Scrips Traded"].toLocaleString();
    summaryCards[4].querySelector(".index-value").textContent =
      summary["Total Market Capitalization Rs:"].toLocaleString();
    summaryCards[5].querySelector(".index-value").textContent =
      summary["Total Float Market Capitalization Rs:"].toLocaleString();
  })
  .catch(err => console.error("Error fetching Market Summary:", err));

async function checkMarketStatus() {
    try {
      const response = await fetch("http://localhost:8000/IsNepseOpen");
      const data = await response.json();

      const statusDiv = document.getElementById("market-status");

      if (data.isOpen === "OPEN") {
        statusDiv.innerHTML = '<span class="dot open"></span> Market is Open';
      } else {
        statusDiv.innerHTML = '<span class="dot closed"></span> Market is Closed';
      }
    } catch (error) {
      console.error("Error fetching market status:", error);
      document.getElementById("market-status").innerHTML =
        '<span class="dot closed"></span> Error fetching status';
    }
  }

  checkMarketStatus();
  setInterval(checkMarketStatus, 60000);