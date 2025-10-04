<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Realtime</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }

        .toast {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            pointer-events: auto;
            cursor: pointer;
            max-width: 350px;
            border-left: 4px solid #3498db;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.hide {
            transform: translateX(400px);
        }

        .toast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .toast-title {
            font-weight: bold;
            font-size: 14px;
            color: #3498db;
        }

        .toast-close {
            background: none;
            border: none;
            color: #95a5a6;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-close:hover {
            color: #e74c3c;
        }

        .toast-body {
            font-size: 13px;
            line-height: 1.4;
            color: #ecf0f1;
        }

        .toast.room-notification {
            border-left-color: #27ae60;
        }

        .toast.call-notification {
            border-left-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* Sidebar */
        .sidebar {
            width: 300px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background: #34495e;
            border-bottom: 1px solid #465c71;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            overflow: hidden;
        }

        .user-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-name {
            font-weight: 600;
        }

        .header-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 10px;
        }

        .test-toast-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .test-toast-btn:hover {
            background: #2980b9;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #ecf0f1;
            cursor: pointer;
            font-size: 14px;
        }

        .rooms-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .room-item {
            padding: 15px;
            margin-bottom: 5px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .room-item:hover {
            background: #34495e;
        }

        .room-item.active {
            background: #3498db;
            border-color: #2980b9;
        }

        .room-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .room-last-message {
            font-size: 12px;
            opacity: 0.8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: auto;
        }

        .new-room-btn {
            margin: 10px;
            padding: 10px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .new-room-btn:hover {
            background: #229954;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 20px;
            background: white;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .room-info h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .room-info p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .call-controls {
            display: flex;
            gap: 10px;
        }

        .call-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .call-btn.video-call {
            background: #27ae60;
            color: white;
        }

        .call-btn.video-call:hover {
            background: #229954;
        }

        .call-btn.audio-call {
            background: #3498db;
            color: white;
        }

        .call-btn.audio-call:hover {
            background: #2980b9;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .message.own {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            color: white;
        }

        .message-content {
            max-width: 60%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
        }

        .message.own .message-content {
            background: #3498db;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message:not(.own) .message-content {
            background: white;
            color: #2c3e50;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .message-sender {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #7f8c8d;
        }

        .message.own .message-sender {
            color: #bdc3c7;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #ecf0f1;
        }

        .input-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #ecf0f1;
            border-radius: 25px;
            font-size: 14px;
            resize: none;
            max-height: 100px;
            min-height: 45px;
        }

        .message-input:focus {
            outline: none;
            border-color: #3498db;
        }

        .send-btn {
            padding: 12px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .send-btn:hover {
            background: #2980b9;
        }

        .send-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
        }

        .modal h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .no-rooms {
            text-align: center;
            padding: 40px;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div id="app">
        <div class="chat-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <img class="user-avatar-img" src="https://scontent.fdad3-6.fna.fbcdn.net/v/t39.30808-6/526583688_1188166543335182_4103561911743280463_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeH-nKrJ0D_Usfdxcykn56mg4L3NfQGK6cDgvc19AYrpwFYzYIVEqpGCrHNine0fDMgBbilnY5NqsdNxoQJo3jbX&_nc_ohc=2ekVOX6fPoIQ7kNvwHcOD2u&_nc_oc=AdmXPcPQARPW-U5bJtyVeBrmbqnfQYYrlemaX08UaoptHegrmSJJvG8Kiuvp5gQ3wN4&_nc_zt=23&_nc_ht=scontent.fdad3-6.fna&_nc_gid=2uwcioa0ibqBp-W0_-D13g&oh=00_Aff8U5vN9811ROT3i_CwcgzQxkNeP0tEdmtAAf3TlEDf0g&oe=68E521F7" alt="">

                        </div>
                        <div>
                            <div class="user-name">@{{ currentUser.ho_va_ten || 'User' }}</div>
                            <div class="header-buttons">
                                <button class="test-toast-btn" @click="testToast" title="Test Toast">
                                    <i class="fas fa-bell"></i>
                                </button>
                                <button class="logout-btn" @click="logout">Đăng xuất</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="new-room-btn" @click="showNewRoomModal = true">
                    <i class="fas fa-plus"></i> Tạo phòng mới
                </button>

                <div class="rooms-list">
                    <div v-if="loading" class="loading">Đang tải...</div>
                    <div v-else-if="rooms.length === 0" class="no-rooms">
                        Chưa có phòng nào
                    </div>
                    <div v-else>
                        <div v-for="room in rooms"
                             :key="room.id"
                             :class="['room-item', { active: selectedRoom && selectedRoom.id === room.id }]"
                             @click="selectRoom(room)">
                            <div class="room-name">@{{ room.name }}</div>
                            <div class="room-last-message" v-if="room.last_message && room.last_message.content">
                                @{{ room.last_message.content }}
                            </div>
                            <div class="unread-badge" v-if="unreadCounts[room.id] && unreadCounts[room.id] > 0">
                                @{{ unreadCounts[room.id] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="chat-main">
                <div v-if="selectedRoom" class="chat-header">
                    <div class="room-info">
                        <h2>@{{ selectedRoom.name }}</h2>
                        <p>@{{ selectedRoom.participants ? selectedRoom.participants.length : 0 }} thành viên</p>
                    </div>
                    <div class="call-controls">
                        <button class="call-btn video-call" @click="initiateVideoCall" title="Gọi video">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="call-btn audio-call" @click="initiateAudioCall" title="Gọi audio">
                            <i class="fas fa-phone"></i>
                        </button>
                    </div>
                </div>

                <div v-if="selectedRoom" class="chat-messages" ref="messagesContainer">
                    <div v-if="messages.length === 0" class="no-messages">
                        Chưa có tin nhắn nào
                    </div>
                    <div v-else>
                        <div v-for="message in messages"
                             :key="message.id"
                             :class="['message', { own: message.sender_id === (currentUser ? currentUser.id : null) }]">
                            <div class="message-avatar">
                                @{{ message.sender && message.sender.ho_va_ten ? message.sender.ho_va_ten.charAt(0) : 'U' }}
                            </div>
                            <div class="message-content">
                                <div class="message-sender" v-if="message.sender_id !== currentUser.id">
                                    @{{ message.sender ? message.sender.ho_va_ten : '' }}
                                </div>
                                <div>@{{ message.content }}</div>
                                <div class="message-time">
                                    @{{ formatTime(message.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="selectedRoom" class="chat-input">
                    <div class="input-container">
                        <textarea
                            class="message-input"
                            v-model="newMessage"
                            @keydown.enter.prevent="sendMessage"
                            placeholder="Nhập tin nhắn..."
                            rows="1"></textarea>
                        <button class="send-btn" @click="sendMessage" :disabled="!newMessage.trim()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Room Modal -->
        <div v-if="showNewRoomModal" class="modal" @click="showNewRoomModal = false">
            <div class="modal-content" @click.stop>
                <h3>Tạo phòng mới</h3>
                <div class="form-group">
                    <label>Tên phòng:</label>
                    <input type="text" v-model="newRoom.name" placeholder="Nhập tên phòng">
                </div>
                <div class="form-group">
                    <label>Mô tả:</label>
                    <textarea v-model="newRoom.description" placeholder="Nhập mô tả (tùy chọn)"></textarea>
                </div>
                <div class="form-group">
                    <label>Loại phòng:</label>
                    <select v-model="newRoom.type">
                        <option value="private">Riêng tư</option>
                        <option value="group">Nhóm</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button class="btn btn-secondary" @click="showNewRoomModal = false">Hủy</button>
                    <button class="btn btn-primary" @click="createRoom" :disabled="!newRoom.name">Tạo phòng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        // Cấu hình axios
        const token = localStorage.getItem('token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
        }

        createApp({
            data() {
                return {
                    currentUser: JSON.parse(localStorage.getItem('user') || '{}'),
                    rooms: [],
                    selectedRoom: null,
                    messages: [],
                    newMessage: '',
                    loading: false,
                    showNewRoomModal: false,
                    newRoom: {
                        name: '',
                        description: '',
                        type: 'private'
                    },
                    unreadCounts: {},
                    pollingInterval: null,
                    fcmToken: null,
                    notificationPermission: 'default',
                    lastMessageId: null,
                    toastNotifications: []
                }
            },
            async mounted() {
                await this.loadRooms();
                this.startPolling();
                await this.initializeNotifications();
                this.startMessagePolling();
            },
            beforeUnmount() {
                this.stopPolling();
            },
            methods: {
                async loadRooms() {
                    try {
                        const response = await axios.get('/api/rooms');
                        this.rooms = response.data.data;
                        await this.loadUnreadCounts();

                        if (this.rooms.length > 0) {
                            this.selectRoom(this.rooms[0]);
                        }

                        // Khởi tạo lastMessageId từ tin nhắn mới nhất
                        await this.initializeLastMessageId();
                    } catch (error) {
                        console.error('Lỗi tải danh sách phòng:', error);
                    }
                },

                                async initializeLastMessageId() {
                    try {
                        const response = await axios.get('/api/messages/latest');
                        const latestMessages = response.data.data;

                        console.log('Initializing lastMessageId with:', latestMessages);

                        if (latestMessages.length > 0) {
                            this.lastMessageId = Math.max(...latestMessages.map(m => m.id));
                            console.log('Initial lastMessageId set to:', this.lastMessageId);
                        } else {
                            this.lastMessageId = 0;
                            console.log('No messages found, lastMessageId set to 0');
                        }
                    } catch (error) {
                        console.error('Lỗi khởi tạo lastMessageId:', error);
                        this.lastMessageId = 0;
                    }
                },

                async selectRoom(room) {
                    this.selectedRoom = room;
                    await this.loadMessages(room.id);
                    await this.markAsRead(room.id);
                    this.scrollToBottom();
                },

                async loadMessages(roomId) {
                    try {
                        const response = await axios.get(`/api/rooms/${roomId}/messages`);
                        this.messages = response.data.data.data.reverse();
                    } catch (error) {
                        console.error('Lỗi tải tin nhắn:', error);
                    }
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || !this.selectedRoom) return;

                    try {
                        const response = await axios.post(`/api/rooms/${this.selectedRoom.id}/messages`, {
                            content: this.newMessage,
                            type: 'text'
                        });

                        this.messages.push(response.data.data);
                        this.newMessage = '';
                        this.scrollToBottom();
                        const roomIndex = this.rooms.findIndex(r => r.id === this.selectedRoom.id);
                        if (roomIndex !== -1) {
                            this.rooms[roomIndex].last_message = response.data.data;
                        }

                    } catch (error) {
                        console.error('Lỗi gửi tin nhắn:', error);
                    }
                },

                async markAsRead(roomId) {
                    try {
                        await axios.post(`/api/rooms/${roomId}/read`);
                        this.unreadCounts[roomId] = 0;
                    } catch (error) {
                        console.error('Lỗi đánh dấu đã đọc:', error);
                    }
                },

                async loadUnreadCounts() {
                    try {
                        const response = await axios.get('/api/unread-count');
                        this.unreadCounts = response.data.data;
                    } catch (error) {
                        console.error('Lỗi tải số tin nhắn chưa đọc:', error);
                    }
                },

                async createRoom() {
                    try {
                        const response = await axios.post('/api/rooms', this.newRoom);
                        this.rooms.push(response.data.data);
                        this.showNewRoomModal = false;
                        this.newRoom = { name: '', description: '', type: 'private' };
                    } catch (error) {
                        console.error('Lỗi tạo phòng:', error);
                    }
                },

                async initiateVideoCall() {
                    try {
                        const response = await axios.post(`/api/rooms/${this.selectedRoom.id}/video-call/initiate`, {
                            type: 'video'
                        });

                        // Mở trang video call trong tab mới
                        window.open(`/video-call?callId=${response.data.data.call_id}`, '_blank');

                    } catch (error) {
                        console.error('Lỗi khởi tạo cuộc gọi video:', error);
                        alert(error.response?.data?.message || 'Không thể khởi tạo cuộc gọi');
                    }
                },

                async initiateAudioCall() {
                    try {
                        const response = await axios.post(`/api/rooms/${this.selectedRoom.id}/video-call/initiate`, {
                            type: 'audio'
                        });

                        // Mở trang video call trong tab mới
                        window.open(`/video-call?callId=${response.data.data.call_id}`, '_blank');

                    } catch (error) {
                        console.error('Lỗi khởi tạo cuộc gọi audio:', error);
                        alert(error.response?.data?.message || 'Không thể khởi tạo cuộc gọi');
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    return date.toLocaleTimeString('vi-VN', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                startPolling() {
                    this.pollingInterval = setInterval(async () => {
                        if (this.selectedRoom) {
                            await this.loadMessages(this.selectedRoom.id);
                        }
                        await this.loadUnreadCounts();
                    }, 3000); // Poll mỗi 3 giây
                },

                stopPolling() {
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                    }
                },

                startMessagePolling() {
                    // Polling để kiểm tra tin nhắn mới từ tất cả phòng
                    setInterval(async () => {
                        await this.checkNewMessages();
                    }, 2000); // Kiểm tra mỗi 2 giây
                },

                async checkNewMessages() {
                    try {
                        const response = await axios.get('/api/messages/latest');
                        const latestMessages = response.data.data;

                        console.log('Latest messages:', latestMessages);
                        console.log('Current lastMessageId:', this.lastMessageId);

                        latestMessages.forEach(message => {
                            // Kiểm tra xem có phải tin nhắn mới không
                            if (this.lastMessageId !== null && message.id > this.lastMessageId) {
                                console.log('New message detected:', message);
                                // Hiển thị toast notification
                                this.showToastNotification(message);
                            }
                        });

                        // Cập nhật lastMessageId
                        if (latestMessages.length > 0) {
                            const maxId = Math.max(...latestMessages.map(m => m.id));
                            if (!this.lastMessageId || maxId > this.lastMessageId) {
                                this.lastMessageId = maxId;
                                console.log('Updated lastMessageId to:', this.lastMessageId);
                            }
                        }
                    } catch (error) {
                        console.error('Lỗi kiểm tra tin nhắn mới:', error);
                    }
                },

                showToastNotification(message) {
                    // Kiểm tra xem có đang ở trong phòng này không
                    const isInCurrentRoom = this.selectedRoom && this.selectedRoom.id === message.room_id;

                    // Chỉ hiển thị toast nếu không đang ở trong phòng đó
                    if (!isInCurrentRoom) {
                        const toast = {
                            id: Date.now(),
                            title: message.sender.ho_va_ten + ' (@' + message.room.name + ')',
                            body: message.content,
                            type: 'message',
                            data: {
                                room_id: message.room_id,
                                sender_id: message.sender_id,
                                message_id: message.id
                            }
                        };

                        this.toastNotifications.push(toast);
                        this.displayToast(toast);
                    }
                },

                displayToast(toast) {
                    console.log('Displaying toast:', toast);

                    const toastContainer = document.getElementById('toastContainer');
                    const toastElement = document.createElement('div');
                    toastElement.className = 'toast room-notification';
                    toastElement.id = `toast-${toast.id}`;

                    toastElement.innerHTML = `
                        <div class="toast-header">
                            <div class="toast-title">${toast.title}</div>
                            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
                        </div>
                        <div class="toast-body">${toast.body}</div>
                    `;

                    toastContainer.appendChild(toastElement);

                    // Hiển thị toast
                    setTimeout(() => {
                        toastElement.classList.add('show');
                    }, 100);

                    // Tự động ẩn sau 5 giây
                    setTimeout(() => {
                        toastElement.classList.add('hide');
                        setTimeout(() => {
                            if (toastElement.parentNode) {
                                toastElement.remove();
                            }
                        }, 300);
                    }, 5000);

                    // Click để mở phòng
                    toastElement.addEventListener('click', () => {
                        this.openRoomFromNotification(toast.data.room_id);
                        toastElement.remove();
                    });
                },

                openRoomFromNotification(roomId) {
                    // Tìm phòng và mở
                    const room = this.rooms.find(r => r.id === roomId);
                    if (room) {
                        this.selectRoom(room);
                    }
                },

                testToast() {
                    console.log('Testing toast notification...');
                    const testToast = {
                        id: Date.now(),
                        title: 'Test Notification',
                        body: 'Đây là thông báo test từ nút bell!',
                        type: 'test',
                        data: {
                            room_id: 1,
                            sender_id: 1,
                            message_id: 1
                        }
                    };

                    this.toastNotifications.push(testToast);
                    this.displayToast(testToast);
                },

                logout() {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/';
                },

                async initializeNotifications() {
                    // Kiểm tra hỗ trợ notifications
                    if (!('Notification' in window)) {
                        console.log('Trình duyệt không hỗ trợ notifications');
                        return;
                    }

                    this.notificationPermission = Notification.permission;

                    if (this.notificationPermission === 'default') {
                        // Yêu cầu quyền
                        const permission = await Notification.requestPermission();
                        this.notificationPermission = permission;
                    }

                    if (this.notificationPermission === 'granted') {
                        // Khởi tạo Firebase Cloud Messaging (nếu có)
                        await this.initializeFCM();
                    }
                },

                async initializeFCM() {
                    // Trong thực tế, bạn sẽ cần Firebase SDK
                    // Đây là demo đơn giản
                    console.log('FCM initialized');

                    // Lưu FCM token (demo)
                    this.fcmToken = 'demo_fcm_token_' + Date.now();

                    try {
                        await axios.post('/api/notifications/fcm-token', {
                            fcm_token: this.fcmToken
                        });
                    } catch (error) {
                        console.error('Lỗi cập nhật FCM token:', error);
                    }
                },

                showNotification(title, body, data = {}) {
                    if (this.notificationPermission === 'granted') {
                        const notification = new Notification(title, {
                            body: body,
                            icon: '/favicon.ico',
                            badge: '/favicon.ico',
                            tag: data.type || 'general',
                            data: data
                        });

                        notification.onclick = function() {
                            window.focus();
                            notification.close();

                            // Xử lý click notification
                            if (data.action === 'open_room' && data.room_id) {
                                // Mở phòng chat
                                console.log('Opening room:', data.room_id);
                            }
                        };

                        // Tự động đóng sau 5 giây
                        setTimeout(() => {
                            notification.close();
                        }, 5000);
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
