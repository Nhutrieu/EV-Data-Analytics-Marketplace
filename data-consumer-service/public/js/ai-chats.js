// Dùng global để không bị "Identifier ... has already been declared"
window.aiChartInstance = window.aiChartInstance || null;

function initAIChat() {
    const chatBtn = document.getElementById('ai-chat-btn');
    const chatPopup = document.getElementById('ai-chat-popup');
    const closeBtn = document.getElementById('ai-close-btn');
    const inputEl = document.getElementById('aiUserInput');
    const sendBtn = document.getElementById('aiSendBtn');
    const messagesEl = document.getElementById('ai-messages');

    if (!chatBtn || !chatPopup || !closeBtn || !inputEl || !sendBtn || !messagesEl) {
        console.warn('AI chat elements not found on this page');
        return;
    }

    chatBtn.addEventListener('click', () => {
        chatPopup.style.display = (chatPopup.style.display === 'flex' ? 'none' : 'flex');
    });

    closeBtn.addEventListener('click', () => {
        chatPopup.style.display = 'none';
    });

    sendBtn.addEventListener('click', sendAIMessage);
    inputEl.addEventListener('keypress', e => {
        if (e.key === 'Enter') sendAIMessage();
    });

    function addMessage(sender, text, type = 'ai') {
        const div = document.createElement('div');
        div.className = `msg ${type}`;
        div.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messagesEl.appendChild(div);
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
            // 🔁 Gọi backend trong Docker – dùng path tuyệt đối, KHÔNG gửi USER_ID nữa
            const res = await fetch('/api/ai-chat.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            });

            if (res.status === 401) {
                messagesEl.removeChild(loadingEl);
                addMessage('AI', 'Bạn chưa đăng nhập. Vui lòng đăng nhập lại.', 'ai');
                return;
            }

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
                renderAIChart(data.reply.chartData.labels, data.reply.chartData.datasets);
            }

            if (data.reply?.alerts) {
                data.reply.alerts.forEach(a =>
                    addMessage('⚠️ Cảnh báo', a, 'alert')
                );
            }

        } catch (err) {
            console.error(err);
            messagesEl.removeChild(loadingEl);
            addMessage('AI', '❌ Lỗi kết nối server');
        }
    }

    function renderAIChart(labels, datasets) {
        const canvas = document.getElementById('aiChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (window.aiChartInstance) {
            window.aiChartInstance.destroy();
        }

        window.aiChartInstance = new Chart(ctx, {
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
    // Chỉ init ở trang có analytics
    if (document.getElementById('analytics-page')) {
        initAIChat();
    }
});
