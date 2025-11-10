// frontend/js/revenue.js

let revenueChart;
let topProductsChart;

// Helper format VND
function formatVND(value) {
    if (value == null) return "0 VND";
    return Number(value).toLocaleString("vi-VN") + " VND";
}

// ---------- BIỂU ĐỒ DOANH THU THEO THÁNG (DỮ LIỆU TỪ DB) ----------

function initRevenueChart(monthly) {
    const ctx = document.getElementById("revenueChart");
    if (!ctx) return;

    // monthly: [{year, month, label, revenue}, ...]
    const labels = monthly.map(m => m.label || ("T" + m.month));
    const data = monthly.map(m => (Number(m.revenue || 0) / 1_000_000)); // chuyển sang "triệu VND"

    const chartData = {
        labels,
        datasets: [
            {
                label: "Doanh thu (triệu VND)",
                data,
                borderColor: "#00d4ff",
                backgroundColor: "rgba(0, 212, 255, 0.1)",
                borderWidth: 3,
                fill: true,
                tension: 0.4,
            },
        ],
    };

    if (revenueChart) {
        revenueChart.destroy();
    }

    revenueChart = new Chart(ctx, {
        type: "line",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: "#94a3b8",
                        font: { size: 12 },
                    },
                },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    borderColor: "#00d4ff",
                    borderWidth: 1,
                    callbacks: {
                        label: function (context) {
                            const v = context.parsed.y || 0;
                            return v.toFixed(2) + "M VND";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: "rgba(100, 116, 139, 0.2)" },
                    ticks: { color: "#94a3b8" },
                },
                y: {
                    grid: { color: "rgba(100, 116, 139, 0.2)" },
                    ticks: {
                        color: "#94a3b8",
                        callback: function (value) {
                            return value + "M";
                        },
                    },
                },
            },
        },
    });
}

// ---------- BIỂU ĐỒ TOP DATASET (DỮ LIỆU TỪ DB by_dataset) ----------

function initTopProductsChart(byDataset) {
    const ctx = document.getElementById("topProductsChart");
    if (!ctx) return;

    const labels = byDataset.map(d => d.name || `Dataset #${d.dataset_id}`);
    const data = byDataset.map(d => Number(d.revenue || 0) / 1_000_000);

    const chartData = {
        labels,
        datasets: [
            {
                label: "Doanh thu (triệu VND)",
                data,
                backgroundColor: [
                    "#2563eb",
                    "#06b6d4",
                    "#00d4ff",
                    "#3b82f6",
                    "#64748b",
                    "#22c55e",
                    "#f97316",
                    "#e11d48",
                ],
                borderColor: "#0f172a",
                borderWidth: 2,
            },
        ],
    };

    if (topProductsChart) {
        topProductsChart.destroy();
    }

    topProductsChart = new Chart(ctx, {
        type: "bar",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    callbacks: {
                        label: function (context) {
                            const v = context.parsed.y || 0;
                            return v.toFixed(2) + "M VND";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: "rgba(100, 116, 139, 0.2)" },
                    ticks: { color: "#94a3b8" },
                },
                y: {
                    grid: { color: "rgba(100, 116, 139, 0.2)" },
                    ticks: {
                        color: "#94a3b8",
                        callback: function (value) {
                            return value + "M";
                        },
                    },
                },
            },
        },
    });
}

// ---------- ĐỔ DỮ LIỆU LÊN CARD + CHART ----------

async function loadRevenueDashboard() {
    try {
        const data = await apiGetRevenueDashboard(); // gọi GET ?page=revenue_api
        const summary = data.summary || {};
        const byDataset = data.by_dataset || [];
        const monthly = data.monthly || [];
        const transactions = data.transactions || [];

        // Cards
        const totalRevenueEl = document.getElementById("revenue-total");
        const downloadsEl = document.getElementById("revenue-downloads");

        if (totalRevenueEl) {
            totalRevenueEl.textContent = formatVND(summary.total_revenue || 0);
        }
        if (downloadsEl) {
            downloadsEl.textContent = summary.total_transactions || 0;
        }

        // Charts
        initRevenueChart(monthly);
        initTopProductsChart(byDataset);
        renderTransactionsTable(transactions);

    } catch (err) {
        console.error("Lỗi load dashboard doanh thu:", err);
        alert("Không tải được dữ liệu doanh thu từ server.");
    }
}

// ---------- ĐỔI TYPE CHART + EXPORT PNG ----------

function updateChartType() {
    if (!revenueChart) return;
    const type = document.getElementById("chart-type").value;
    revenueChart.config.type = type;
    revenueChart.update();
}
window.updateChartType = updateChartType;

function exportChart() {
    if (!revenueChart) return;
    const link = document.createElement("a");
    link.download = "doanh-thu-ev-data.png";
    link.href = revenueChart.toBase64Image();
    link.click();
}
window.exportChart = exportChart;

// ---------- INIT ----------

document.addEventListener("DOMContentLoaded", () => {
    loadRevenueDashboard();
});
function renderTransactionsTable(list) {
    const tbody = document.getElementById("revenue-transactions-body");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!list || list.length === 0) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 6;
        td.textContent = "Chưa có giao dịch nào.";
        td.style.textAlign = "center";
        td.style.color = "#64748b";
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    list.forEach(tx => {
        const tr = document.createElement("tr");

        // Ngày
        const tdDate = document.createElement("td");
        tdDate.textContent = tx.timestamp
            ? new Date(tx.timestamp).toLocaleString("vi-VN")
            : "-";

        // Bộ dữ liệu
        const tdDataset = document.createElement("td");
        tdDataset.textContent = tx.dataset_name || "(Không rõ)";

        // Khách hàng
        const tdBuyer = document.createElement("td");
        tdBuyer.textContent = tx.buyer || "-";

        // Loại (method)
        const tdType = document.createElement("td");
        tdType.textContent = tx.method || "-";

        // Số tiền
        const tdAmount = document.createElement("td");
        tdAmount.textContent = formatVND(tx.amount || 0);

        // Trạng thái
        const tdStatus = document.createElement("td");
        tdStatus.textContent = tx.status || "-";

        tr.appendChild(tdDate);
        tr.appendChild(tdDataset);
        tr.appendChild(tdBuyer);
        tr.appendChild(tdType);
        tr.appendChild(tdAmount);
        tr.appendChild(tdStatus);

        tbody.appendChild(tr);
    });
}
