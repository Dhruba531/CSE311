<style>
/* Chatbot Floating Button */
.chat-trigger {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: #000;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: transform 0.2s;
}
.chat-trigger:hover {
    transform: scale(1.1);
}

/* Chat Window */
.chat-window {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    font-family: 'Inter', sans-serif;
    border: 1px solid #f4f4f5;
    opacity: 0;
    pointer-events: none;
    transform: translateY(20px);
    transition: all 0.3s;
}
.chat-window.active {
    opacity: 1;
    pointer-events: all;
    transform: translateY(0);
}

/* Header */
.chat-header {
    background: #000;
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.chat-header h4 { margin: 0; font-size: 16px; font-weight: 700; }
.chat-header span { font-size: 12px; opacity: 0.7; }

/* Messages Area */
.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #fcfcfc;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.message {
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.4;
}
.message.bot {
    background: #f4f4f5;
    color: #18181b;
    align-self: flex-start;
    border-top-left-radius: 2px;
}
.message.user {
    background: #000;
    color: white;
    align-self: flex-end;
    border-top-right-radius: 2px;
}

/* Input Area */
.chat-input-area {
    padding: 15px;
    border-top: 1px solid #f4f4f5;
    display: flex;
    gap: 10px;
    background: white;
}
.chat-input-area input {
    flex: 1;
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 14px;
}
.chat-input-area button {
    background: none;
    border: none;
    color: #000;
    font-weight: 700;
    cursor: pointer;
}
</style>

<!-- Floating Trigger -->
<div class="chat-trigger" onclick="toggleChat()">
    <i class="fas fa-comment-alt"></i>
</div>

<!-- Chat Interface -->
<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <div>
            <h4>XTrade Support</h4>
            <span>Avg. reply: Instant</span>
        </div>
        <i class="fas fa-times" onclick="toggleChat()" style="cursor:pointer;"></i>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="message bot">
            Hello! I'm your AI assistant. Ask me anything about trading, accounts, or setup! 👋
        </div>
    </div>
    
    <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="Type your question..." onkeypress="handleEnter(event)">
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
function toggleChat() {
    document.getElementById('chatWindow').classList.toggle('active');
    document.getElementById('chatInput').focus();
}

function handleEnter(e) {
    if (e.key === 'Enter') sendMessage();
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const msgs = document.getElementById('chatMessages');
    const txt = input.value.trim();
    
    if (txt === '') return;
    
    // Add User Message
    const uDiv = document.createElement('div');
    uDiv.className = 'message user';
    uDiv.textContent = txt;
    msgs.appendChild(uDiv);
    
    input.value = '';
    msgs.scrollTop = msgs.scrollHeight;
    
    // Simulate Bot Delay
    setTimeout(() => {
        const bDiv = document.createElement('div');
        bDiv.className = 'message bot';
        bDiv.innerHTML = getBotResponse(txt);
        msgs.appendChild(bDiv);
        msgs.scrollTop = msgs.scrollHeight;
    }, 600);
}

function getBotResponse(q) {
    q = q.toLowerCase();
    
    if (q.includes('buy') || q.includes('trade')) 
        return "To buy stocks, go to the <b>Market</b> page. Click 'Buy' next to any stock, enter the quantity, and confirm.";
    
    if (q.includes('sell')) 
        return "To sell, visit the <b>Market</b> page. If you own shares, a Red 'Sell' button will appear next to the stock.";
    
    if (q.includes('money') || q.includes('deposit') || q.includes('fund')) 
        return "You can deposit funds in your <b>Portfolio</b>. Use the 'Quick Deposit' form on the left side.";
    
    if (q.includes('2fa') || q.includes('security')) 
        return "Go to <b>Admin/Setup</b> links provided, or check <a href='setup_2fa.php'>Security Settings</a> to enable 2-Factor Authentication.";
    
    if (q.includes('password') || q.includes('reset')) 
        return "If you forgot your password, please contact support or delete your account via database reset for now (Demo Mode).";
    
    if (q.includes('hello') || q.includes('hi')) 
        return "Hi there! How can I help you today?";
        
    return "I'm not sure about that. Try asking about 'buying', 'selling', 'deposits', or '2fa'.";
}
</script>
