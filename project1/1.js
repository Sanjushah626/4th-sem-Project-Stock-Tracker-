
function toggleStocks(id) {
var stocks = document.getElementById("stocks-" + id);
if (!stocks) return;
stocks.style.display = stocks.style.display === "block" ? "none" : "block";
}
var portfolioModal = document.getElementById("portfolioModal");
var addPortfolioBtn = document.getElementById("addPortfolioBtn");
var closePortfolio = document.getElementById("closePortfolio");
addPortfolioBtn.addEventListener("click", function () { portfolioModal.style.display = "block"; });
closePortfolio.addEventListener("click", function () { portfolioModal.style.display = "none"; });
var stockModal = document.getElementById("addStockModal");
var closeStock = document.getElementById("closeStock");
var purchaseTypeSelect = document.getElementById("purchase_type");
var buyPriceField = document.getElementById("buyPriceField");
var buyPriceInput = document.getElementById("buy_price");
var typeHint = document.getElementById("typeHint");
document.querySelectorAll(".add-stock-btn").forEach(function(button) {
button.addEventListener("click", function (e) {
e.stopPropagation();
var portfolioId = this.closest(".portfolio-card").dataset.id;
document.getElementById("portfolio_id").value = portfolioId;
stockModal.style.display = "block";
loadSymbolList();
});
});
closeStock.addEventListener("click", function () { stockModal.style.display = "none"; });
function applyTransactionTypeRules() {
var type = purchaseTypeSelect.value;
if (type === "Bonus") {
buyPriceField.classList.add("field-hidden");
buyPriceInput.required = false;
buyPriceInput.value = 0;
typeHint.textContent = "Bonus shares are received at zero cost and carry no buying charges. Only quantity is recorded.";
} else if (type === "IPO") {
buyPriceField.classList.remove("field-hidden");
buyPriceInput.required = true;
typeHint.textContent = "IPO allotment — no broker commission, SEBON fee or DP charge on buying.";
} else {
buyPriceField.classList.remove("field-hidden");
buyPriceInput.required = true;
typeHint.textContent = "Secondary market purchase — broker commission, SEBON fee & DP charge apply.";
}
}
purchaseTypeSelect.addEventListener("change", applyTransactionTypeRules);
applyTransactionTypeRules();
var sellModal = document.getElementById("sellStockModal");
var closeSell = document.getElementById("closeSell");
closeSell.addEventListener("click", function () { sellModal.style.display = "none"; });
function openSellModal(stockId, symbol, availableQty) {
document.getElementById("sell_stock_id").value = stockId;
document.getElementById("sell_symbol").value = symbol;
document.getElementById("sell_available").value = availableQty;
var qtyInput = document.getElementById("sell_quantity");
qtyInput.value = "";
qtyInput.max = availableQty;
document.getElementById("sell_price").value = "";
document.getElementById("sell_date").value = "";
sellModal.style.display = "block";
}
window.addEventListener("click", function (e) {
if (e.target === portfolioModal) portfolioModal.style.display = "none";
if (e.target === stockModal) stockModal.style.display = "none";
if (e.target === sellModal) sellModal.style.display = "none";
});
var symbolListLoaded = false;
async function loadSymbolList() {
if (symbolListLoaded) return;
try {
var res = await fetch("http://localhost:8000/PriceVolume");
var stocks = await res.json();
var datalist = document.getElementById("symbolList");
datalist.innerHTML = "";
stocks.forEach(function(item) {
if (!item.symbol) return;
var opt = document.createElement("option");
opt.value = item.symbol.toUpperCase();
var label = item.securityName || item.companyName || item.securityDesc || "";
if (label) opt.label = item.symbol.toUpperCase() + " — " + label;
datalist.appendChild(opt);
});
symbolListLoaded = true;
} catch (err) {
console.error("Could not load symbol list:", err);
}
}
function formatMoney(num) {
var value = Number(num) || 0;
return value.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function calculateBrokerCommission(amount) {
amount = Number(amount) || 0;
var rate;
if (amount <= 50000) rate = 0.0036;
else if (amount <= 500000) rate = 0.0033;
else if (amount <= 2000000) rate = 0.0031;
else if (amount <= 10000000) rate = 0.0027;
else rate = 0.0024;
var commission = amount * rate;
if (commission < 10) commission = 10;
return commission;
}
function calculateSebonFee(amount) {
amount = Number(amount) || 0;
return amount * 0.00015;
}
function calculateDpCharge() { return 25; }
function calculateBuyCharges(amount, purchaseType) {
var type = (purchaseType || "").toLowerCase();
if (type === "ipo" || type === "bonus") return { broker: 0, sebon: 0, dp: 0, total: 0 };
var broker = calculateBrokerCommission(amount);
var sebon = calculateSebonFee(amount);
var dp = calculateDpCharge();
return { broker: broker, sebon: sebon, dp: dp, total: broker + sebon + dp };
}
function calculateSellCharges(amount) {
var broker = calculateBrokerCommission(amount);
var sebon = calculateSebonFee(amount);
var dp = calculateDpCharge();
return { broker: broker, sebon: sebon, dp: dp, total: broker + sebon + dp };
}
function getCapitalGainTaxRate(buyDateStr, portfolioType) {
var type = (portfolioType || "personal").toLowerCase();
if (type === "institutional") return 0.10;
var buyDate = new Date(buyDateStr);
var today = new Date();
if (isNaN(buyDate.getTime())) return 0.10;
var msPerDay = 1000 * 60 * 60 * 24;
var holdingDays = Math.floor((today - buyDate) / msPerDay);
return holdingDays >= 365 ? 0.075 : 0.10;
}
function getPortfolioType(card) {
if (card.dataset && card.dataset.type) return card.dataset.type.toLowerCase();
var heading = card.querySelector("h3");
if (heading) {
var match = heading.textContent.match(/\(([^)]+)\)/);
if (match && match[1]) return match[1].trim().toLowerCase();
}
return "personal";
}

function calculateStockBreakdown(row, ltp, portfolioType) {
var qty = parseFloat(row.dataset.quantity) || 0;
if (qty === 0) {
return {
investment: 0, currentValue: 0, receivableAmount: 0,
grossProfit: 0, capitalGainTax: 0, netProfit: 0, profitPercent: 0,
buyCharges: { broker: 0, sebon: 0, dp: 0, total: 0 },
sellCharges: { broker: 0, sebon: 0, dp: 0, total: 0 }
};
}
var buyPrice = parseFloat(row.dataset.buyprice) || 0;
var buyDate = row.dataset.buydate || "";
var purchaseType = row.dataset.purchasetype || "secondary";
var investment = qty * buyPrice;
var currentValue = qty * ltp;
var buyCharges = calculateBuyCharges(investment, purchaseType);
var sellCharges = calculateSellCharges(currentValue);
var receivableAmount = currentValue - sellCharges.total;
var totalCost = investment + buyCharges.total;
var grossProfit = receivableAmount - totalCost;
var taxRate = getCapitalGainTaxRate(buyDate, portfolioType);
var capitalGainTax = grossProfit > 0 ? grossProfit * taxRate : 0;
var netProfit = grossProfit - capitalGainTax;
var profitPercent = totalCost > 0 ? (netProfit / totalCost) * 100 : 0;
return {
investment: investment, currentValue: currentValue, receivableAmount: receivableAmount,
grossProfit: grossProfit, capitalGainTax: capitalGainTax, netProfit: netProfit,
profitPercent: profitPercent, buyCharges: buyCharges, sellCharges: sellCharges
};
}
async function updateStockPrices() {
try {
var response = await fetch("http://localhost:8000/PriceVolume");
if (!response.ok) throw new Error("Unable to fetch API");
var stocks = await response.json();
console.log(stocks);
stocks.forEach(function(item) {
var symbol = item.symbol.toUpperCase();
var priceCell = document.querySelector('.stock-ltp[data-symbol="' + symbol + '"]');
var card = priceCell ? priceCell.closest(".stock-card") : null;
if (!card) return;
var qty = parseFloat(card.dataset.quantity) || 0;
if (qty === 0) {
priceCell.innerText = "-";
return;
}
var ltp = parseFloat(item.lastTradedPrice) || 0;
var previousClose = parseFloat(item.previousClose) || 0;
var closePrice = parseFloat(item.closePrice) || 0;
var percentageChange = parseFloat(item.percentageChange) || 0;
var todaysGain = qty * (closePrice - previousClose);
var portfolioCard = card.closest(".portfolio-card");
var portfolioType = portfolioCard ? getPortfolioType(portfolioCard) : "personal";
var breakdown = calculateStockBreakdown(card, ltp, portfolioType);
var totalInvestment = breakdown.investment + breakdown.buyCharges.total;
var receivableAmount = breakdown.receivableAmount;
var profitLoss = breakdown.netProfit;
priceCell.innerText = formatMoney(ltp);
priceCell.classList.remove("loading");
var pctBadge = card.querySelector(".stock-pct-change");
if (pctBadge) {
pctBadge.innerText = percentageChange.toFixed(2) + "%";
pctBadge.className = "stock-pct-change " + (percentageChange >= 0 ? "up" : "down");
}
var investmentCell = card.querySelector(".investment");
if (investmentCell) {
investmentCell.innerText = formatMoney(totalInvestment);
investmentCell.title = "Raw Investment (qty x avg buy price): Rs. " + formatMoney(breakdown.investment) + "\n" +
"Broker Commission: Rs. " + formatMoney(breakdown.buyCharges.broker) + "\n" +
"SEBON Fee: Rs. " + formatMoney(breakdown.buyCharges.sebon) + "\n" +
"DP Charge: Rs. " + formatMoney(breakdown.buyCharges.dp) + "\n" +
"Total Investment: Rs. " + formatMoney(totalInvestment);
}
var currentValueCell = card.querySelector(".current-value");
if (currentValueCell) {
currentValueCell.innerText = formatMoney(breakdown.currentValue);
currentValueCell.title = "Market Value (qty x LTP), before any selling charges.";
}
var receivableCell = card.querySelector(".receivable-amount");
if (receivableCell) {
receivableCell.innerText = formatMoney(receivableAmount);
receivableCell.title = "Market Value (qty x LTP): Rs. " + formatMoney(breakdown.currentValue) + "\n" +
"Broker Commission (if sold now): Rs. " + formatMoney(breakdown.sellCharges.broker) + "\n" +
"SEBON Fee: Rs. " + formatMoney(breakdown.sellCharges.sebon) + "\n" +
"DP Charge: Rs. " + formatMoney(breakdown.sellCharges.dp) + "\n" +
"Receivable Amount (net): Rs. " + formatMoney(receivableAmount);
}
var plValue = card.querySelector(".profit-loss");
var plPercent = card.querySelector(".profit-percent");
var profitBar = card.querySelector(".stock-profit-bar");
if (plValue) {
plValue.innerText = formatMoney(profitLoss);
plValue.className = "profit-bar-value profit-loss " + (profitLoss >= 0 ? "positive" : "negative");
plValue.title = "Total Investment (incl. charges): Rs. " + formatMoney(totalInvestment) + "\n" +
"Receivable Amount (after sell charges): Rs. " + formatMoney(receivableAmount) + "\n" +
"Gross Profit: Rs. " + formatMoney(breakdown.grossProfit) + "\n" +
"Capital Gain Tax: Rs. " + formatMoney(breakdown.capitalGainTax) + "\n" +
"Net Profit (after tax): Rs. " + formatMoney(breakdown.netProfit) + "\n" +
"Profit %: " + breakdown.profitPercent.toFixed(2) + "%";
}
if (plPercent) {
plPercent.innerText = breakdown.profitPercent.toFixed(2) + "%";
plPercent.className = "profit-bar-percent profit-percent " + (profitLoss >= 0 ? "positive" : "negative");
}
if (profitBar) {
profitBar.className = "stock-profit-bar " + (profitLoss >= 0 ? "positive" : "negative");
}
var tg = card.querySelector(".todays-gain");
if (tg) {
tg.innerText = formatMoney(todaysGain);
tg.className = "stat-value todays-gain " + (todaysGain >= 0 ? "positive" : "negative");
}
});
document.querySelectorAll(".portfolio-card").forEach(function(card) {
var totalInvestment = 0;
var totalCurrentValue = 0;
var totalReceivableAmount = 0;
var totalProfitLoss = 0;
var totalUnits = 0;
var totalTodaysGain = 0;
var totalPreviousValue = 0;
card.querySelectorAll(".stock-card[data-quantity]").forEach(function(stockCard) {
var qty = parseFloat(stockCard.dataset.quantity) || 0;
totalUnits += qty;
var investmentText = stockCard.querySelector(".investment")?.innerText || "0";
var currentValueText = stockCard.querySelector(".current-value")?.innerText || "0";
var receivableText = stockCard.querySelector(".receivable-amount")?.innerText || "0";
var profitLossText = stockCard.querySelector(".profit-loss")?.innerText || "0";
var prevCloseText = stockCard.querySelector(".prev-close")?.innerText || "0";
var todaysGainText = stockCard.querySelector(".todays-gain")?.innerText || "0";
var investment = parseFloat(investmentText.replace(/,/g, "")) || 0;
var currentValue = parseFloat(currentValueText.replace(/,/g, "")) || 0;
var receivable = parseFloat(receivableText.replace(/,/g, "")) || 0;
var profitLoss = parseFloat(profitLossText.replace(/,/g, "")) || 0;
var previousClose = parseFloat(prevCloseText.replace(/,/g, "")) || 0;
var todaysGain = parseFloat(todaysGainText.replace(/,/g, "")) || 0;
totalInvestment += investment;
totalCurrentValue += currentValue;
totalReceivableAmount += receivable;
totalProfitLoss += profitLoss;
totalTodaysGain += todaysGain;
totalPreviousValue += previousClose * qty;
});
var totalProfitPercent = totalInvestment > 0 ? (totalProfitLoss / totalInvestment) * 100 : 0;
var todaysGainPercent = totalPreviousValue > 0 ? (totalTodaysGain / totalPreviousValue) * 100 : 0;
var metrics = card.querySelectorAll(".metrics div");
if (metrics.length >= 8) {
metrics[0].innerHTML = "<strong>Total Investment</strong>" +
'<span class="metric-value neutral">Rs. ' + formatMoney(totalInvestment) + '</span>';
metrics[1].innerHTML = "<strong>Profit / Loss</strong>" +
'<span class="metric-value ' + (totalProfitLoss >= 0 ? 'positive' : 'negative') + '">Rs. ' + formatMoney(totalProfitLoss) + '</span>';
metrics[2].innerHTML = "<strong>Current Units</strong>" +
'<span class="metric-value neutral">' + totalUnits + '</span>';
metrics[3].innerHTML = "<strong>Current Value</strong>" +
'<span class="metric-value neutral">Rs. ' + formatMoney(totalCurrentValue) + '</span>';
metrics[4].innerHTML = "<strong>Receivable Amount</strong>" +
'<span class="metric-value neutral">Rs. ' + formatMoney(totalReceivableAmount) + '</span>';
metrics[5].innerHTML = "<strong>Total Profit %</strong>" +
'<span class="metric-value ' + (totalProfitPercent >= 0 ? 'positive' : 'negative') + '">' + totalProfitPercent.toFixed(2) + '%</span>';
metrics[6].innerHTML = "<strong>Today\'s Gain</strong>" +
'<span class="metric-value ' + (totalTodaysGain >= 0 ? 'positive' : 'negative') + '">Rs. ' + formatMoney(totalTodaysGain) + '</span>';
metrics[7].innerHTML = "<strong>Today\'s Gain %</strong>" +
'<span class="metric-value ' + (todaysGainPercent >= 0 ? 'positive' : 'negative') + '">' + todaysGainPercent.toFixed(2) + '%</span>';
}
});
} catch (error) {
console.error("API Error:", error);
document.querySelectorAll(".stock-ltp.loading").forEach(function(cell) {
cell.innerText = "N/A";
});
}
}
updateStockPrices();
setInterval(updateStockPrices, 30000);
async function checkMarketStatus() {
try {
var response = await fetch("http://localhost:8000/IsNepseOpen");
var data = await response.json();
var statusDiv = document.getElementById("market-status");
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

/* ===== MARKET DEPTH MODAL FUNCTIONS ===== */
var depthModal = document.getElementById("depthModal");
var currentDepthSymbol = "";
var currentDepthLTP = 0;

function openDepthModal(symbol) {
  currentDepthSymbol = symbol;
  document.getElementById("depthSymbol").textContent = symbol;
  document.getElementById("buySymbol").textContent = symbol;
  document.getElementById("sellSymbol").textContent = symbol;
  depthModal.style.display = "block";
  document.getElementById("depthLoading").style.display = "block";
  document.getElementById("depthTableWrap").style.display = "none";
  document.getElementById("depthClosed").style.display = "none";
  loadMarketDepth(symbol);
}

function closeDepthModal() {
  depthModal.style.display = "none";
}

// Close on outside click
window.addEventListener("click", function(e) {
  if (e.target === depthModal) closeDepthModal();
});

// Close on Escape key
document.addEventListener("keydown", function(e) {
  if (e.key === "Escape" && depthModal.style.display === "block") {
    closeDepthModal();
  }
});

function loadMarketDepth(symbol) {
  // Fetch both market depth and current price simultaneously
  Promise.all([
    fetch("http://127.0.0.1:8000/MarketDepth?symbol=" + encodeURIComponent(symbol)),
    fetch("http://127.0.0.1:8000/PriceVolume")
  ])
    .then(function(responses) {
      return Promise.all(responses.map(function(r) {
        if (!r.ok) throw new Error("API Error: " + r.status);
        return r.json();
      }));
    })
    .then(function(results) {
      var depthData = results[0];
      var priceData = results[1];

      document.getElementById("depthLoading").style.display = "none";

      var buyList = (depthData.marketDepth && depthData.marketDepth.buyMarketDepthList) ? depthData.marketDepth.buyMarketDepthList : [];
      var sellList = (depthData.marketDepth && depthData.marketDepth.sellMarketDepthList) ? depthData.marketDepth.sellMarketDepthList : [];

      if (buyList.length === 0 && sellList.length === 0) {
        document.getElementById("depthClosed").style.display = "block";
        document.getElementById("depthTableWrap").style.display = "none";
        return;
      }

      document.getElementById("depthTableWrap").style.display = "block";

      // Find LTP from PriceVolume API
      currentDepthLTP = 0;
      var pctChange = 0;
      if (Array.isArray(priceData)) {
        var stockInfo = priceData.find(function(item) {
          return item.symbol && item.symbol.toUpperCase() === symbol.toUpperCase();
        });
        if (stockInfo) {
          currentDepthLTP = parseFloat(stockInfo.lastTradedPrice) || 0;
          pctChange = parseFloat(stockInfo.percentageChange) || 0;
        }
      }

      // Update header with LTP
      var ltpHtml = "";
      if (currentDepthLTP > 0) {
        var pctClass = pctChange >= 0 ? "positive" : "negative";
        var pctSign = pctChange >= 0 ? "+" : "";
        ltpHtml = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">' +
          '<span style="font-size:28px;font-weight:700;color:var(--text-primary);">Rs. ' + formatMoney(currentDepthLTP) + '</span>' +
          '<span style="font-size:14px;font-weight:700;padding:5px 12px;border-radius:8px;background:' + (pctChange >= 0 ? 'var(--green-soft)' : 'var(--red-soft)') + ';color:' + (pctChange >= 0 ? 'var(--green)' : 'var(--red)') + ';">' +
          pctSign + pctChange.toFixed(2) + '%</span>' +
          '</div>';
      }

      // Insert LTP before the table
      var tableWrap = document.getElementById("depthTableWrap");
      var existingLtp = document.getElementById("depthLTP");
      if (existingLtp) existingLtp.remove();
      var ltpDiv = document.createElement("div");
      ltpDiv.id = "depthLTP";
      ltpDiv.innerHTML = ltpHtml;
      tableWrap.insertBefore(ltpDiv, tableWrap.firstChild);

      var tbody = document.getElementById("depthTableBody");
      tbody.innerHTML = "";

      for (var i = 0; i < 5; i++) {
        var buy = buyList[i] || {};
        var sell = sellList[i] || {};
        var row = document.createElement("tr");
        // FIXED: use orderCount instead of orders, orderBookOrderPrice instead of price
        row.innerHTML =
          '<td class="buy-cell">' + (buy.orderCount != null ? buy.orderCount : "-") + '</td>' +
          '<td class="buy-cell">' + (buy.quantity != null ? Number(buy.quantity).toLocaleString() : "-") + '</td>' +
          '<td class="buy-cell">' + (buy.orderBookOrderPrice != null ? buy.orderBookOrderPrice : "-") + '</td>' +
          '<td class="sell-cell">' + (sell.orderBookOrderPrice != null ? sell.orderBookOrderPrice : "-") + '</td>' +
          '<td class="sell-cell">' + (sell.quantity != null ? Number(sell.quantity).toLocaleString() : "-") + '</td>' +
          '<td class="sell-cell">' + (sell.orderCount != null ? sell.orderCount : "-") + '</td>';
        tbody.appendChild(row);
      }

      // FIXED: Use totalBuyQty and totalSellQty from root level instead of recalculating
      document.getElementById("totalBuyQty").textContent = Number(depthData.totalBuyQty || 0).toLocaleString();
      document.getElementById("totalSellQty").textContent = Number(depthData.totalSellQty || 0).toLocaleString();
    })
    .catch(function(err) {
      console.error("Depth API Error:", err);
      document.getElementById("depthLoading").style.display = "none";
      document.getElementById("depthClosed").style.display = "block";
      document.getElementById("depthTableWrap").style.display = "none";
    });
}

function handleDepthBuy() {
  window.open("https://tms87.nepsetms.com.np/tms/me/memberclientorderentry?symbol=" + encodeURIComponent(currentDepthSymbol) + "&transaction=buy", "_blank");
}

function handleDepthSell() {
  window.open("https://tms87.nepsetms.com.np/tms/me/memberclientorderentry?symbol=" + encodeURIComponent(currentDepthSymbol) + "&transaction=sell", "_blank");
}

