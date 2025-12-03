<!-- Chat AI Button -->
<button
  class="fixed bottom-5 right-5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-0 rounded-full w-16 h-16 flex items-center justify-center cursor-pointer shadow-lg z-[1000] transition-transform hover:scale-105"
  onclick="toggleChatAI()"
>
  <span class="text-2xl">💬</span>
</button>

<!-- Chat AI Container -->
<div
  id="chatAIContainer"
  class="fixed bottom-20 right-5 w-[350px] h-[600px] bg-white rounded-[15px] shadow-2xl hidden flex flex-col z-[1000] overflow-hidden"
>
  <!-- Resize handle (bên trái) -->
  <div
    id="resizeLeft"
    class="absolute left-0 top-0 w-[12px] h-full cursor-ew-resize bg-gray-300 opacity-60 hover:opacity-90"
    title="Kéo để thay đổi kích thước"
  ></div>

  <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-[15px] flex items-center justify-between">
    <h3 class="m-0 text-[18px]">Chat với AI</h3>
    <button class="bg-transparent border-0 text-white text-[20px] cursor-pointer" onclick="toggleChatAI()">&times;</button>
  </div>

  <!-- Chat messages -->
  <div
    id="chatMessages"
    class="flex-1 min-h-0 p-[15px] pb-[35px] overflow-y-auto bg-gray-100"
    style="scroll-behavior: smooth;"
  >
    <div
      class="message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words"
    >
      Xin chào! Tôi là AI hỗ trợ của Code Cùng Sang. Bạn cần giúp đỡ về khóa học nào?
    </div>
  </div>

  <!-- Input -->
  <div class="flex p-[15px] border-t border-gray-200 bg-white">
    <input
      type="text"
      id="chatInput"
      placeholder="Nhập tin nhắn..."
      class="flex-1 border border-gray-300 rounded-[20px] px-[15px] py-[10px] outline-none"
      onkeypress="handleKeyPress(event)"
    />
    <button
      class="bg-indigo-500 text-white border-0 rounded-[20px] px-[15px] py-[10px] ml-[10px] cursor-pointer"
      onclick="sendMessage()"
    >
      Gửi
    </button>
  </div>
</div>
<script>
  // Tạo session ID nếu chưa có (dùng localStorage để persistent qua refresh)
  let sessionId = localStorage.getItem('chatSessionId');
  if (!sessionId) {
    sessionId = 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('chatSessionId', sessionId);
  }

  /* ====== MỞ / ĐÓNG CHAT & LOAD HISTORY ====== */
  function toggleChatAI() {
    const container = document.getElementById('chatAIContainer');
    container.classList.toggle('hidden');

    if (!container.classList.contains('hidden')) {
      loadChatHistory();  // Load lịch sử khi mở
      scrollToBottom();
    }
  }

  /* ====== LOAD LỊCH SỬ CHAT ====== */
  async function loadChatHistory() {
    const messages = document.getElementById('chatMessages');
    messages.innerHTML = '';  // Clear để load mới

    try {
      const response = await fetch('./chat-handler.php?load_history=1&session_id=' + encodeURIComponent(sessionId), {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });

      if (!response.ok) throw new Error('Load history failed');

      const data = await response.json();
      if (data.history && Array.isArray(data.history)) {
        data.history.forEach(msg => {
          const div = document.createElement('div');
          if (msg.role === 'user') {
            div.className = 'message user-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-indigo-500 text-white ml-auto text-right break-words';
          } else {
            div.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
          }
          div.textContent = msg.message;
          messages.appendChild(div);
        });
      }

      // Thêm welcome message nếu history rỗng
      if ((data.history || []).length === 0) {
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
        welcomeDiv.textContent = 'Xin chào! Tôi là AI hỗ trợ của Code Cùng Sang. Bạn cần giúp đỡ về khóa học nào?';
        messages.appendChild(welcomeDiv);
      }

      scrollToBottom();
    } catch (error) {
      console.error('Load history error:', error);
      // Fallback welcome message
      const welcomeDiv = document.createElement('div');
      welcomeDiv.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
      welcomeDiv.textContent = 'Xin chào! Tôi là AI hỗ trợ của Code Cùng Sang. Bạn cần giúp đỡ về khóa học nào?';
      messages.appendChild(welcomeDiv);
    }
  }

  /* ====== SCROLL TO BOTTOM ====== */
  function scrollToBottom() {
    const messages = document.getElementById('chatMessages');
    if (!messages) return;
    setTimeout(() => { messages.scrollTop = messages.scrollHeight; }, 100);
  }

  /* ====== HIỆU ỨNG TYPING (CHẠY TỪNG CHỮ) ====== */
  function typeWriter(element, text, speed = 30) {  // speed: ms giữa các chữ (thấp hơn = nhanh hơn)
    let i = 0;
    element.textContent = '';  // Clear nội dung

    function type() {
      if (i < text.length) {
        element.textContent += text.charAt(i);
        i++;
        scrollToBottom();  // Scroll theo typing
        setTimeout(type, speed);
      } else {
        // Xong typing, thêm blinking cursor nếu muốn (tùy chọn)
        // element.innerHTML += '<span class="blinking-cursor">|</span>';
      }
    }
    type();
  }

  /* ====== GỬI TIN NHẮN ====== */
  async function sendMessage() {
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const userMessage = input.value.trim();
    if (!userMessage) return;

    // Hiển thị user message ngay
    const userDiv = document.createElement('div');
    userDiv.className = 'message user-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-indigo-500 text-white ml-auto text-right break-words';
    userDiv.textContent = userMessage;
    messages.appendChild(userDiv);
    scrollToBottom();

    input.value = '';

    // Loading
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingIndicator';
    loadingDiv.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
    loadingDiv.textContent = 'Đang suy nghĩ...';
    messages.appendChild(loadingDiv);
    scrollToBottom();

    try {
      const response = await fetch('./chat-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          message: userMessage, 
          session_id: sessionId  // Gửi session_id để lưu DB
        }),
      });

      loadingDiv.remove();  // Xóa loading

      const data = await response.json();

      if (data.error) throw new Error(data.error);

      // Tạo div cho AI reply
      const aiDiv = document.createElement('div');
      aiDiv.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
      messages.appendChild(aiDiv);

      // Áp dụng typing effect
      typeWriter(aiDiv, data.reply, 25);  // 25ms/chữ = tốc độ tự nhiên

    } catch (error) {
      loadingDiv.remove();

      const errorDiv = document.createElement('div');
      errorDiv.className = 'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-red-100 text-red-700 border border-red-200 break-words';
      errorDiv.textContent = 'Lỗi: ' + error.message + '. Thử lại sau!';
      messages.appendChild(errorDiv);
      scrollToBottom();
    }
  }

  function handleKeyPress(event) {
    if (event.key === 'Enter') sendMessage();
  }

  /* ====== RESIZE BÊN TRÁI ====== */
  const resizeLeft = document.getElementById("resizeLeft");
  const chatBox = document.getElementById("chatAIContainer");
  let resizingLeft = false, startX = 0, startWidth = 0;

  resizeLeft.addEventListener("mousedown", function (e) {
    resizingLeft = true;
    startX = e.clientX;
    startWidth = chatBox.offsetWidth;
    document.body.style.userSelect = "none";
  });

  document.addEventListener("mousemove", function (e) {
    if (!resizingLeft) return;
    const change = startX - e.clientX;
    const newWidth = startWidth + change;
    if (newWidth > 280 && newWidth < 900) chatBox.style.width = newWidth + "px";
  });

  document.addEventListener("mouseup", function () {
    resizingLeft = false;
    document.body.style.userSelect = "auto";
  });

  // Load history khi trang load (nếu chat đang mở)
  if (!document.getElementById('chatAIContainer').classList.contains('hidden')) {
    loadChatHistory();
  }

  // Tùy chọn: Thêm CSS cho blinking cursor (nếu dùng)
  const style = document.createElement('style');
  style.textContent = `
    .blinking-cursor {
      animation: blink 1s infinite;
    }
    @keyframes blink {
      0%, 50% { opacity: 1; }
      51%, 100% { opacity: 0; }
    }
  `;
  document.head.appendChild(style);
</script>