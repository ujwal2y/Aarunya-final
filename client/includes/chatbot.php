<!-- Aarunya Floating Chatbot Component -->
<div id="aarunya-chatbot">
    <!-- Emergency Call Button (Secondary) -->
    <button id="emergency-call-btn" class="emergency-call-btn" onclick="handleEmergencyCall()" title="Emergency Help">
        <i class="fas fa-phone-alt"></i>
    </button>

    <!-- Chat Button (Collapsed State) -->
    <button id="chat-toggle-btn" class="chat-toggle-btn" onclick="toggleChatbot()">
        <i class="fas fa-comments"></i>
        <span class="chat-badge" id="chat-badge">1</span>
    </button>

    <!-- Chat Window (Expanded State) -->
    <div id="chat-window" class="chat-window">
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-avatar">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <div class="chat-header-info">
                    <h4>Aarunya Assistant</h4>
                    <span class="chat-status">
                        <span class="status-dot"></span> Online
                    </span>
                </div>
            </div>
            <div class="chat-header-actions">
                <button class="emergency-header-btn" onclick="handleEmergencyCall()" title="Emergency Help">
                    <i class="fas fa-ambulance"></i>
                </button>
                <button class="chat-close-btn" onclick="toggleChatbot()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Chat Body -->
        <div class="chat-body" id="chat-body">
            <!-- Welcome Message -->
            <div class="chat-message bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <p>Hello! 👋 I'm your Aarunya Assistant. How can I help you today?</p>
                    </div>
                    <span class="message-time">Just now</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions" id="quick-actions">
                <button class="quick-action-btn" onclick="sendQuickMessage('Book an appointment')">
                    <i class="fas fa-calendar-check"></i> Book Appointment
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('Find a doctor')">
                    <i class="fas fa-user-doctor"></i> Find Doctor
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('Pregnancy tips')">
                    <i class="fas fa-lightbulb"></i> Pregnancy Tips
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('Emergency help')">
                    <i class="fas fa-ambulance"></i> Emergency
                </button>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typing-indicator" style="display: none;">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="typing-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
            <input 
                type="text" 
                id="chat-input" 
                class="chat-input" 
                placeholder="Type your message..."
                onkeypress="handleKeyPress(event)"
            >
            <button class="chat-send-btn" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* ============================================
   CHATBOT STYLES
   ============================================ */

#aarunya-chatbot {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: var(--font-family, 'Inter', sans-serif);
}

/* ============================================
   EMERGENCY CALL BUTTON (SECONDARY)
   ============================================ */

.emergency-call-btn {
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    transition: all 0.3s ease;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse-red 2s infinite;
}

.emergency-call-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
}

@keyframes pulse-red {
    0%, 100% {
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.8);
    }
}

/* ============================================
   CHAT TOGGLE BUTTON (COLLAPSED STATE)
   ============================================ */

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    border: none;
    color: #000;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(244, 114, 182, 0.4);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(244, 114, 182, 0.6);
}

.chat-toggle-btn:active {
    transform: scale(0.95);
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    border: 2px solid #e0f2f1;
}

/* ============================================
   CHAT WINDOW (EXPANDED STATE)
   ============================================ */

.chat-window {
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 350px;
    max-width: calc(100vw - 40px);
    height: 500px;
    max-height: calc(100vh - 140px);
    background: rgba(15, 23, 42, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(244, 114, 182, 0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

.chat-window.active {
    display: flex;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================================
   CHAT HEADER
   ============================================ */

.chat-header {
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.chat-header-info h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #000;
}

.chat-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: rgba(0, 0, 0, 0.7);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.chat-header-actions {
    display: flex;
    gap: 8px;
}

.emergency-header-btn,
.chat-close-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.2);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.emergency-header-btn {
    background: rgba(239, 68, 68, 0.3);
}

.emergency-header-btn:hover {
    background: rgba(239, 68, 68, 0.5);
    transform: scale(1.1);
}

.chat-close-btn:hover {
    background: rgba(0, 0, 0, 0.4);
    transform: rotate(90deg);
}

/* ============================================
   CHAT BODY
   ============================================ */

.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #e0f2f1;
}

.chat-body::-webkit-scrollbar {
    width: 6px;
}

.chat-body::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.chat-body::-webkit-scrollbar-thumb {
    background: rgba(244, 114, 182, 0.3);
    border-radius: 3px;
}

.chat-body::-webkit-scrollbar-thumb:hover {
    background: rgba(244, 114, 182, 0.5);
}

/* ============================================
   CHAT MESSAGES
   ============================================ */

.chat-message {
    display: flex;
    gap: 10px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bot-message {
    align-self: flex-start;
}

.user-message {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(244, 114, 182, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C4A7FF;
    font-size: 14px;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 70%;
}

.message-bubble {
    background: rgba(255, 255, 255, 0.05);
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.user-message .message-bubble {
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    color: #000;
    border: none;
}

.message-bubble p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #fff;
}

.user-message .message-bubble p {
    color: #000;
}

.message-time {
    font-size: 11px;
    color: #78909c;
    padding: 0 4px;
}

.user-message .message-time {
    text-align: right;
}

/* ============================================
   QUICK ACTIONS
   ============================================ */

.quick-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 8px;
}

.quick-action-btn {
    padding: 10px 12px;
    background: rgba(244, 114, 182, 0.1);
    border: 1px solid rgba(244, 114, 182, 0.3);
    border-radius: 8px;
    color: #C4A7FF;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}

.quick-action-btn:hover {
    background: rgba(244, 114, 182, 0.2);
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 14px;
}

/* ============================================
   TYPING INDICATOR
   ============================================ */

.typing-indicator {
    display: flex;
    gap: 10px;
    padding: 0 16px 16px;
    animation: fadeIn 0.3s ease;
}

.typing-dots {
    display: flex;
    gap: 4px;
    align-items: center;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #C4A7FF;
    animation: typing 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        opacity: 0.3;
        transform: scale(0.8);
    }
    30% {
        opacity: 1;
        transform: scale(1);
    }
}

/* ============================================
   INPUT AREA
   ============================================ */

.chat-input-area {
    padding: 16px;
    background: rgba(0, 0, 0, 0.3);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    gap: 8px;
}

.chat-input {
    flex: 1;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: #fff;
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease;
}

.chat-input:focus {
    border-color: #C4A7FF;
    box-shadow: 0 0 0 3px rgba(244, 114, 182, 0.1);
}

.chat-input::placeholder {
    color: #78909c;
}

.chat-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    border: none;
    color: #000;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.chat-send-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(244, 114, 182, 0.4);
}

.chat-send-btn:active {
    transform: scale(0.95);
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */

@media (max-width: 768px) {
    .chat-window {
        width: calc(100vw - 40px);
        height: calc(100vh - 140px);
        bottom: 90px;
    }

    .emergency-call-btn {
        bottom: 90px;
        width: 45px;
        height: 45px;
        font-size: 18px;
    }

    .chat-toggle-btn {
        width: 55px;
        height: 55px;
        font-size: 22px;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .message-content {
        max-width: 80%;
    }
}

@media (max-width: 480px) {
    #aarunya-chatbot {
        bottom: 15px;
        right: 15px;
    }

    .chat-window {
        right: 15px;
        bottom: 80px;
    }

    .emergency-call-btn {
        right: 15px;
        bottom: 80px;
    }
}
</style>

<script>
/* ============================================
   CHATBOT JAVASCRIPT
   ============================================ */

// Chatbot state
let isChatOpen = false;
let messageCount = 0;

// Toggle chatbot window
function toggleChatbot() {
    const chatWindow = document.getElementById('chat-window');
    const chatBtn = document.getElementById('chat-toggle-btn');
    const badge = document.getElementById('chat-badge');
    
    isChatOpen = !isChatOpen;
    
    if (isChatOpen) {
        chatWindow.classList.add('active');
        chatBtn.innerHTML = '<i class="fas fa-times"></i>';
        badge.style.display = 'none';
        scrollToBottom();
    } else {
        chatWindow.classList.remove('active');
        chatBtn.innerHTML = '<i class="fas fa-comments"></i>';
    }
}

// Send message
function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (message === '') return;
    
    // Add user message
    addMessage(message, 'user');
    
    // Clear input
    input.value = '';
    
    // Hide quick actions after first message
    const quickActions = document.getElementById('quick-actions');
    if (quickActions) {
        quickActions.style.display = 'none';
    }
    
    // Show typing indicator
    showTypingIndicator();
    
    // Simulate bot response
    setTimeout(() => {
        hideTypingIndicator();
        const botResponse = getBotResponse(message);
        addMessage(botResponse, 'bot');
    }, 1500);
}

// Handle Enter key press
function handleKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

// Send quick message
function sendQuickMessage(message) {
    const input = document.getElementById('chat-input');
    input.value = message;
    sendMessage();
}

// Add message to chat
function addMessage(text, sender) {
    const chatBody = document.getElementById('chat-body');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${sender}-message`;
    
    const time = new Date().toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    const avatarIcon = sender === 'user' ? 'fa-user' : 'fa-robot';
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas ${avatarIcon}"></i>
        </div>
        <div class="message-content">
            <div class="message-bubble">
                <p>${text}</p>
            </div>
            <span class="message-time">${time}</span>
        </div>
    `;
    
    chatBody.appendChild(messageDiv);
    scrollToBottom();
    messageCount++;
}

// Show typing indicator
function showTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    indicator.style.display = 'flex';
    scrollToBottom();
}

// Hide typing indicator
function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    indicator.style.display = 'none';
}

// Scroll to bottom of chat
function scrollToBottom() {
    const chatBody = document.getElementById('chat-body');
    setTimeout(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 100);
}

// Get bot response (predefined logic)
function getBotResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    // Greetings
    if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
        return "Hello! 😊 How can I assist you with your pregnancy journey today?";
    }
    
    // Appointment booking
    if (message.includes('appointment') || message.includes('book')) {
        return "I can help you book an appointment! 📅 Please visit our <a href='book-appointment.php' style='color: #C4A7FF;'>Appointment Booking</a> page or would you like me to guide you through the process?";
    }
    
    // Find doctor
    if (message.includes('doctor') || message.includes('specialist')) {
        return "We have experienced maternal health specialists available! 👨‍⚕️ You can browse our <a href='doctors.php' style='color: #C4A7FF;'>Doctors</a> page to find the right specialist for you.";
    }
    
    // Pregnancy tips
    if (message.includes('tip') || message.includes('advice') || message.includes('pregnancy')) {
        return "Here are some important pregnancy tips: 🤰<br>• Stay hydrated (8-10 glasses of water daily)<br>• Eat nutritious meals<br>• Get regular prenatal checkups<br>• Rest adequately<br>• Light exercise (with doctor's approval)<br><br>Would you like more specific guidance?";
    }
    
    // Emergency
    if (message.includes('emergency') || message.includes('urgent') || message.includes('help')) {
        return "⚠️ If this is a medical emergency, please call our emergency hotline immediately at <a href='tel:+911234567890' style='color: #ef4444; font-weight: bold;'>+91-123-456-7890</a> or click the emergency button above!";
    }
    
    // Health tracking
    if (message.includes('health') || message.includes('track') || message.includes('record')) {
        return "You can track your health vitals and pregnancy progress on our <a href='health.php' style='color: #C4A7FF;'>Health Tracking</a> page. Regular monitoring helps ensure a healthy pregnancy! 💚";
    }
    
    // Due date
    if (message.includes('due date') || message.includes('delivery')) {
        return "Your due date and pregnancy timeline are available on your <a href='dashboard.php' style='color: #C4A7FF;'>Dashboard</a>. Make sure to attend all scheduled prenatal visits! 📆";
    }
    
    // Thank you
    if (message.includes('thank') || message.includes('thanks')) {
        return "You're welcome! 😊 I'm here to help anytime. Take care of yourself and your baby! 💚";
    }
    
    // Default response
    return "I'm here to help! I can assist you with:<br>• Booking appointments 📅<br>• Finding doctors 👨‍⚕️<br>• Pregnancy tips 🤰<br>• Health tracking 💚<br>• Emergency assistance ⚠️<br><br>What would you like to know?";
}

// Handle emergency call
function handleEmergencyCall() {
    const confirmed = confirm(
        "⚠️ EMERGENCY CALL\n\n" +
        "You are about to call the Aarunya emergency hotline.\n\n" +
        "Emergency Number: +91-123-456-7890\n\n" +
        "Click OK to proceed with the call."
    );
    
    if (confirmed) {
        // Trigger phone call
        window.location.href = 'tel:+911234567890';
    }
}

// Initialize chatbot
document.addEventListener('DOMContentLoaded', function() {
    // Show notification badge
    setTimeout(() => {
        const badge = document.getElementById('chat-badge');
        if (badge && !isChatOpen) {
            badge.style.display = 'flex';
        }
    }, 3000);
});
</script>

