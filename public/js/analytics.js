document.addEventListener("DOMContentLoaded", () => {
    const apiUrl = "http://localhost/EV-Data-Analytics-Marketplace/backend/data-consumer-service/index.php?page=analytics_data";

    fetch(apiUrl)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data.length) {
                // Sắp xếp dữ liệu theo ngày tăng dần
                const sortedData = res.data.slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                // Trục X là ngày dạng dd/mm
                const labels = sortedData.map(d => new Date(d.created_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }));

                // Tooltip hiển thị ngày đầy đủ dd/mm/yyyy
                const tooltipLabels = sortedData.map(d => new Date(d.created_at).toLocaleDateString('vi-VN'));

                // Tính dữ liệu trung bình
                const getAvg = arr => arr.reduce((a, b) => a + b, 0) / arr.length;
                const socData = sortedData.map(d => getAvg(JSON.parse(d.soc)));
                const sohData = sortedData.map(d => getAvg(JSON.parse(d.soh)));
                const rangeData = sortedData.map(d => getAvg(JSON.parse(d.range)));
                const consumptionData = sortedData.map(d => getAvg(JSON.parse(d.consumption)));
                const co2Data = sortedData.map(d => getAvg(JSON.parse(d.co2_saved)));

                // Hàm tạo chart tiện lợi
                const createChart = (ctx, type, label, data, color) => {
                    return new Chart(ctx, {
                        type,
                        data: { labels, datasets: [{ label, data, borderColor: color, backgroundColor: color, fill: type === 'line' ? false : true }] },
                        options: {
                            responsive: true,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        title: (tooltipItems) => tooltipLabels[tooltipItems[0].dataIndex],
                                        label: (tooltipItem) => `${tooltipItem.dataset.label}: ${tooltipItem.formattedValue}`
                                    }
                                }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                };

                // Vẽ chart
                createChart(document.getElementById("socChart"), "line", "SoC (%)", socData, "blue");
                createChart(document.getElementById("chargingFreqChart"), "line", "SoH (%)", sohData, "green");
                createChart(document.getElementById("rangeChart"), "bar", "Quãng đường (km)", rangeData, "orange");
                createChart(document.getElementById("consumptionChart"), "bar", "Tiêu thụ năng lượng (kWh)", consumptionData, "red");
                createChart(document.getElementById("co2Chart"), "line", "CO₂ tiết kiệm (kg)", co2Data, "brown");

                // Chart phân bố loại xe
                const vehicleData = sortedData.map(d => JSON.parse(d.vehicle_type));
                const vehicleLabels = Object.keys(vehicleData[0]);
                const vehicleValues = vehicleLabels.map(label => vehicleData.reduce((sum, v) => sum + v[label], 0) / vehicleData.length);

                new Chart(document.getElementById("vehicleTypeChart"), {
                    type: "doughnut",
                    data: { labels: vehicleLabels, datasets: [{ label: "Loại xe (%)", data: vehicleValues, backgroundColor: ["#007bff", "#28a745"] }] },
                    options: { responsive: true }
                });

                // Mở khóa các chart
                document.querySelectorAll(".chart-card.locked").forEach(card => {
                    card.classList.remove("locked");
                    card.querySelector(".lock-icon").style.display = "none";
                });

            } else {
                console.error("Không có dữ liệu analytics:", res);
            }
        })
        .catch(err => console.error("Lỗi fetch API:", err));
});
