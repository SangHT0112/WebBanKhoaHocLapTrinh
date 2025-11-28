<!-- Chat AI Button -->
<button
  class="fixed bottom-5 right-5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-0 rounded-full w-16 h-16 flex items-center justify-center cursor-pointer shadow-lg z-[1000] transition-transform hover:scale-105"
  onclick="toggleChatAI()"
>
  <span class="text-2xl">💬</span>
</button>

<!-- Chat AI Container -->
<div
  class="fixed bottom-[90px] right-5 w-[350px] h-[550px] bg-white rounded-[15px] shadow-2xl hidden flex flex-col z-[999] overflow-hidden"
  id="chatAIContainer"
>
  <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-[15px] flex items-center justify-between">
    <h3 class="m-0 text-[18px]">Chat với AI</h3>
    <button class="bg-transparent border-0 text-white text-[20px] cursor-pointer" onclick="toggleChatAI()">&times;</button>
  </div>

  <!-- Chat messages (FIX SCROLL) -->
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
  function toggleChatAI() {
    const container = document.getElementById('chatAIContainer');
    if (!container) {
      console.error('❌ Chat container not found!');  // Debug nếu thiếu element
      return;
    }
    console.log('🔄 Toggling chat...');  // Debug log
    container.classList.toggle('hidden');

    // Scroll xuống bottom khi mở
    if (!container.classList.contains('hidden')) {
      scrollToBottom();
    }
  }

  // ScrollToBottom cải thiện (tăng delay, check điều kiện)
  function scrollToBottom() {
    const messages = document.getElementById('chatMessages');
    if (!messages) return;

    setTimeout(() => {
      if (messages.scrollHeight > messages.scrollTop) {
        messages.scrollTop = messages.scrollHeight;
      }
    }, 100);  // Tăng delay để DOM ổn định hơn
  }

  async function sendMessage() {
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const userMessage = input.value.trim();
    if (!userMessage) return;

    // User message (thêm break-words)
    const userDiv = document.createElement('div');
    userDiv.className =
      'message user-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-indigo-500 text-white ml-auto text-right break-words';
    userDiv.textContent = userMessage;
    messages.appendChild(userDiv);
    scrollToBottom();

    input.value = '';

    // Loading indicator (thêm break-words cho nhất quán)
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingIndicator';
    loadingDiv.className =
      'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
    loadingDiv.textContent = 'Đang suy nghĩ...';
    messages.appendChild(loadingDiv);
    scrollToBottom();

    try {
      const response = await fetch('./chat-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: userMessage }),
      });

      if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);

      const data = await response.json();

      // Xóa loading an toàn
      const currentLoading = document.getElementById('loadingIndicator');
      if (currentLoading && currentLoading.parentNode === messages) {
        messages.removeChild(currentLoading);
      }

      if (data.error) throw new Error(data.error);

      // AI response (thêm break-words)
      const aiDiv = document.createElement('div');
      aiDiv.className =
        'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-white text-gray-700 border border-gray-200 break-words';
      aiDiv.textContent = data.reply;
      messages.appendChild(aiDiv);
      scrollToBottom();
    } catch (error) {
      // Xóa loading trong catch
      const currentLoading = document.getElementById('loadingIndicator');
      if (currentLoading && currentLoading.parentNode === messages) {
        messages.removeChild(currentLoading);
      }

      console.error('Chat error:', error);
      const errorDiv = document.createElement('div');
      errorDiv.className =
        'message ai-message mb-[10px] p-[10px] rounded-[10px] max-w-[80%] bg-red-100 text-red-700 border border-red-200 break-words';
      errorDiv.textContent = 'Lỗi: ' + error.message + '. Thử lại sau!';
      messages.appendChild(errorDiv);
      scrollToBottom();
    }
  }

  function handleKeyPress(event) {
    if (event.key === 'Enter') sendMessage();
  }
</script>