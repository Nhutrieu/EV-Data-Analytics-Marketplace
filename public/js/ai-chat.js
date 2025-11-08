let aiChartInstance;

function initAIChat() {
    const chatBtn = document.getElementById('ai-chat-btn');
    const chatPopup = document.getElementById('ai-chat-popup');
    const closeBtn = document.getElementById('ai-close-btn');
    const inputEl = document.getElementById('aiUserInput');
    const sendBtn = document.getElementById('aiSendBtn');
    const messagesEl = document.getElementById('ai-messages');

    chatBtn.addEventListener('click', () => {
        chatPopup.style.display = chatPopup.style.display === 'flex' ? 'none' : 'flex';
    });
    closeBtn.addEventListener('click', () => chatPopup.style.display = 'none');
    sendBtn.addEventListener('click', sendAIMessage);
    inputEl.addEventListener('keypress', e => { if (e.key === 'Enter') sendAIMessage(); });

    function addMessage(sender, text, type = 'ai') {
        const p = document.createElement('div');
        p.className = `msg ${type}`;
        p.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messagesEl.appendChild(p);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function sendAIMessage() {
        const msg = inputEl.value.trim();
        if (!msg) return;
        addMessage('Bạn', msg, 'user');
        inputEl.value = '';

        const loadingEl = document.createElement('div');
        loadingEl.className = 'msg ai';
        loadingEl.innerHTML = '<em>Đang trả lời...</em>';
        messagesEl.appendChild(loadingEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const res = await fetch('/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg, user_id: USER_ID })
            });

            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error', e, text);
                loadingEl.innerHTML = '❌ Lỗi response server';
                return;
            }

            messagesEl.removeChild(loadingEl);
            addMessage('AI', data.reply?.text || 'AI chưa trả lời được');

            if (data.reply?.chartData && Object.keys(data.reply.chartData).length) {
                renderChart(data.reply.chartData.labels, data.reply.chartData.datasets);
            }

            if (data.reply?.alerts) {
                data.reply.alerts.forEach(a => addMessage('⚠️ Cảnh báo', a, 'alert'));
            }

        } catch (err) {
            console.error(err);
            messagesEl.removeChild(loadingEl);
            addMessage('AI', '❌ Lỗi kết nối server');
        }
    }

    function renderChart(labels, datasets) {
        const ctx = document.getElementById('aiChart').getContext('2d');
        if (aiChartInstance) aiChartInstance.destroy();
        aiChartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('analytics-page')) initAIChat();
});