import express from "express";
import http from "http";
import { Server } from "socket.io";
import fs from 'fs';

const app = express();
app.use(express.json());

// Tạo HTTP server từ Express
const server = http.createServer(app);

// Socket.io
const io = new Server(server, {
  cors: { origin: "*" }
});

// Lưu socket theo user_id
const users = {}; // { user_id: socket.id }

// Socket.io connection
io.on("connection", (socket) => {
  console.log("New socket connected:", socket.id);

  // Lắng nghe event frontend gửi user_id
  socket.on("register_user", (user_id) => {
    users[user_id] = socket.id;
    console.log("User registered for realtime:", user_id);
  });

  socket.on("disconnect", () => {
    // Xóa socket khỏi users
    for (const uid in users) {
      if (users[uid] === socket.id) {
        delete users[uid];
        console.log("User disconnected:", uid);
        break;
      }
    }
  });
});

// Route PHP gửi notify
app.post("/notify", (req, res) => {
  const { user_id, message } = req.body;
  // Ghi log đơn giản vào file để tiện debug
  try {
    fs.appendFileSync('realtime_notify.log', `${new Date().toISOString()} - notify user:${user_id} message:${message}\n`);
  } catch (e) {
    console.warn('Không thể ghi log notify:', e.message);
  }

  if (users[user_id]) {
    // Phát cả tên event mới (payment_success) và event cũ (notify) để tương thích
    io.to(users[user_id]).emit("payment_success", { message });
    io.to(users[user_id]).emit("notify", message);
    console.log("Notify sent to user:", user_id, message);
  } else {
    console.log("User not connected:", user_id);
  }

  res.json({ status: "sent" });
});

// Start server
server.listen(3001, () => {
  console.log("Realtime server running on 3001");
});
