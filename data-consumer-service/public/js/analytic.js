document.addEventListener("DOMContentLoaded", () => {
    const apiUrl = "/index.php?page=analytics_data";

    const showToast = (message) => {
        const toast = document.getElementById("toast");
        if (!toast) {
            console.log("TOAST:", message);
            return;
        }
        toast.textContent = message;
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 2500);
    };

    const unlockCharts = () => {
        document.querySelectorAll(".chart-card.locked").forEach((card) => {
            card.classList.remove("locked");
            const lockIcon = card.querySelector(".lock-icon");
            if (lockIcon) lockIcon.style.display = "none";
        });
    };

    fetch(apiUrl, { credentials: "include" })
        .then((res) => {
            if (res.status === 401) {
                showToast("Bạn chưa đăng nhập. Một số phân tích có thể bị khóa.");
            }
            return res.json();
        })
        .then((res) => {
            if (!res.success || !Array.isArray(res.data) || !res.data.length) {
                console.error("Không có dữ liệu analytics:", res);
                showToast("Hiện chưa có dữ liệu phân tích để hiển thị.");
                return;
            }

            // Sắp xếp dữ liệu theo ngày tăng dần
            const sortedData = res.data
                .slice()
                .sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );

            // Trục X là ngày dạng dd/mm
            const labels = sortedData.map((d) =>
                new Date(d.created_at).toLocaleDateString("vi-VN", {
                    day: "2-digit",
                    month: "2-digit",
                })
            );

            // Tooltip hiển thị ngày đầy đủ dd/mm/yyyy
            const tooltipLabels = sortedData.map((d) =>
                new Date(d.created_at).toLocaleDateString("vi-VN")
            );

            // Tính dữ liệu trung bình
            const getAvg = (arr) => {
                if (!arr || !arr.length) return 0;
                return (
                    arr.reduce((a, b) => a + b, 0) / Math.max(arr.length, 1)
                );
            };

            const socData = sortedData.map((d) =>
                getAvg(JSON.parse(d.soc || "[]"))
            );
            const sohData = sortedData.map((d) =>
                getAvg(JSON.parse(d.soh || "[]"))
            );
            const rangeData = sortedData.map((d) =>
                getAvg(JSON.parse(d.range || "[]"))
            );
            const consumptionData = sortedData.map((d) =>
                getAvg(JSON.parse(d.consumption || "[]"))
            );
            const co2Data = sortedData.map((d) =>
                getAvg(JSON.parse(d.co2_saved || "[]"))
            );

            // Hàm tạo chart tiện lợi
            const createChart = (canvasId, type, label, data, color) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                const ctx = canvas.getContext("2d");
                return new Chart(ctx, {
                    type,
                    data: {
                        labels,
                        datasets: [
                            {
                                label,
                                data,
                                borderColor: color,
                                backgroundColor:
                                    type === "line"
                                        ? color
                                        : `${color}33`, // chút trong suốt
                                fill: type !== "line",
                                tension: 0.3,
                                pointRadius: 2,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: {
                                    color: "#e5e7eb",
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    title: (items) =>
                                        tooltipLabels[
                                        items[0].dataIndex
                                        ] || "",
                                    label: (item) =>
                                        `${item.dataset.label}: ${item.formattedValue}`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: { color: "#9ca3af" },
                                grid: { color: "rgba(55,65,81,0.3)" },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: "#9ca3af" },
                                grid: { color: "rgba(55,65,81,0.3)" },
                            },
                        },
                    },
                });
            };

            // Vẽ chart
            createChart(
                "socChart",
                "line",
                "SoC (%)",
                socData,
                "rgba(59,130,246,1)" // blue
            );
            createChart(
                "chargingFreqChart",
                "line",
                "SoH (%)",
                sohData,
                "rgba(34,197,94,1)" // green
            );
            createChart(
                "rangeChart",
                "bar",
                "Quãng đường (km)",
                rangeData,
                "rgba(249,115,22,1)" // orange
            );
            createChart(
                "consumptionChart",
                "bar",
                "Tiêu thụ năng lượng (kWh)",
                consumptionData,
                "rgba(239,68,68,1)" // red
            );
            createChart(
                "co2Chart",
                "line",
                "CO₂ tiết kiệm (kg)",
                co2Data,
                "rgba(129,140,248,1)" // indigo
            );

            // Chart phân bố loại xe
            const vehicleData = sortedData.map((d) =>
                JSON.parse(d.vehicle_type || "{}")
            );
            if (vehicleData.length > 0) {
                const vehicleLabels = Object.keys(vehicleData[0] || {});
                const vehicleValues = vehicleLabels.map((label) =>
                    vehicleData.reduce(
                        (sum, v) => sum + (v[label] || 0),
                        0
                    ) / Math.max(vehicleData.length, 1)
                );

                const vehicleCanvas =
                    document.getElementById("vehicleTypeChart");
                if (vehicleCanvas) {
                    const vctx = vehicleCanvas.getContext("2d");
                    new Chart(vctx, {
                        type: "doughnut",
                        data: {
                            labels: vehicleLabels,
                            datasets: [
                                {
                                    label: "Loại xe (%)",
                                    data: vehicleValues,
                                    backgroundColor: [
                                        "#3b82f6",
                                        "#22c55e",
                                        "#eab308",
                                        "#ec4899",
                                    ],
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    labels: { color: "#e5e7eb" },
                                },
                            },
                        },
                    });
                }
            }

            // Mở khóa các chart
            unlockCharts();
        })
        .catch((err) => {
            console.error("Lỗi fetch API analytics:", err);
            showToast("Không tải được dữ liệu analytics. Kiểm tra console.");
        });
});
