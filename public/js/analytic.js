document.addEventListener("DOMContentLoaded", () => {
    // Gọi qua router backend trong Docker
    const apiUrl = "/backend/data-consumer-service/index.php?page=analytics_data";

    fetch(apiUrl)
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data) && res.data.length) {

                // Sắp xếp dữ liệu theo ngày tăng dần
                const sortedData = res.data
                    .slice()
                    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                // Trục X là ngày dạng dd/mm
                const labels = sortedData.map(d =>
                    new Date(d.created_at).toLocaleDateString('vi-VN', {
                        day: '2-digit',
                        month: '2-digit'
                    })
                );

                // Tooltip hiển thị ngày đầy đủ dd/mm/yyyy
                const tooltipLabels = sortedData.map(d =>
                    new Date(d.created_at).toLocaleDateString('vi-VN')
                );

                // Tính dữ liệu trung bình
                const getAvg = arr => {
                    if (!arr || !arr.length) return 0;
                    return arr.reduce((a, b) => a + b, 0) / arr.length;
                };

                const socData = sortedData.map(d => getAvg(JSON.parse(d.soc)));
                const sohData = sortedData.map(d => getAvg(JSON.parse(d.soh)));
                const rangeData = sortedData.map(d => getAvg(JSON.parse(d.range)));
                const consumptionData = sortedData.map(d => getAvg(JSON.parse(d.consumption)));
                const co2Data = sortedData.map(d => getAvg(JSON.parse(d.co2_saved)));

                // Hàm tạo chart tiện lợi
                const createChart = (canvasId, type, label, data, color) => {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) return;

                    const ctx = canvas.getContext("2d");
                    return new Chart(ctx, {
                        type,
                        data: {
                            labels,
                            datasets: [{
                                label,
                                data,
                                borderColor: color,
                                backgroundColor: color,
                                fill: type === 'line' ? false : true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        title: (tooltipItems) =>
                                            tooltipLabels[tooltipItems[0].dataIndex],
                                        label: (tooltipItem) =>
                                            `${tooltipItem.dataset.label}: ${tooltipItem.formattedValue}`
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
                createChart("socChart", "line", "SoC (%)", socData, "blue");
                createChart("chargingFreqChart", "line", "SoH (%)", sohData, "green");
                createChart("rangeChart", "bar", "Quãng đường (km)", rangeData, "orange");
                createChart("consumptionChart", "bar", "Tiêu thụ năng lượng (kWh)", consumptionData, "red");
                createChart("co2Chart", "line", "CO₂ tiết kiệm (kg)", co2Data, "brown");

                // Chart phân bố loại xe
                const vehicleData = sortedData.map(d => JSON.parse(d.vehicle_type));
                if (vehicleData.length > 0) {
                    const vehicleLabels = Object.keys(vehicleData[0] || {});
                    const vehicleValues = vehicleLabels.map(label =>
                        vehicleData.reduce((sum, v) => sum + (v[label] || 0), 0) / vehicleData.length
                    );

                    const vehicleCanvas = document.getElementById("vehicleTypeChart");
                    if (vehicleCanvas) {
                        const vctx = vehicleCanvas.getContext("2d");
                        new Chart(vctx, {
                            type: "doughnut",
                            data: {
                                labels: vehicleLabels,
                                datasets: [{
                                    label: "Loại xe (%)",
                                    data: vehicleValues,
                                    backgroundColor: ["#007bff", "#28a745", "#ffc107", "#dc3545"]
                                }]
                            },
                            options: { responsive: true }
                        });
                    }
                }

                // Mở khóa các chart
                document.querySelectorAll(".chart-card.locked").forEach(card => {
                    card.classList.remove("locked");
                    const lockIcon = card.querySelector(".lock-icon");
                    if (lockIcon) lockIcon.style.display = "none";
                });

            } else {
                console.error("Không có dữ liệu analytics:", res);
            }
        })
        .catch(err => console.error("Lỗi fetch API:", err));
});
