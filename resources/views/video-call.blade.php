<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: white;
            height: 100vh;
            overflow: hidden;
        }

        .video-call-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .video-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px;
            padding: 20px;
            background: #000;
        }

        .video-item {
            position: relative;
            background: #2a2a2a;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 16/9;
        }

        .video-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-item.own {
            border: 3px solid #3498db;
        }

        .video-overlay {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .controls {
            background: #2c3e50;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .control-btn.primary {
            background: #3498db;
            color: white;
        }

        .control-btn.primary:hover {
            background: #2980b9;
        }

        .control-btn.danger {
            background: #e74c3c;
            color: white;
        }

        .control-btn.danger:hover {
            background: #c0392b;
        }

        .control-btn.secondary {
            background: #95a5a6;
            color: white;
        }

        .control-btn.secondary:hover {
            background: #7f8c8d;
        }

        .control-btn.muted {
            background: #e74c3c;
        }

        .control-btn.video-off {
            background: #e74c3c;
        }

        .call-info {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            padding: 15px;
            border-radius: 10px;
            z-index: 100;
        }

        .call-info h3 {
            margin-bottom: 5px;
            color: #3498db;
        }

        .participants-list {
            font-size: 12px;
            opacity: 0.8;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-size: 18px;
        }

        .error {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #e74c3c;
            font-size: 18px;
        }

        .incoming-call {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #2c3e50;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            z-index: 1000;
        }

        .incoming-call h3 {
            margin-bottom: 20px;
            color: #3498db;
        }

        .incoming-call-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
    <div id="app">
        <div v-if="loading" class="loading">
            Đang kết nối cuộc gọi...
        </div>

        <div v-else-if="error" class="error">
            @{{ error }}
        </div>

        <div v-else-if="incomingCall" class="incoming-call">
            <h3>Cuộc gọi đến</h3>
            <p>@{{ incomingCall.initiator.ho_va_ten }} đang gọi bạn</p>
            <div class="incoming-call-buttons">
                <button class="btn btn-success" @click="acceptCall">Trả lời</button>
                <button class="btn btn-danger" @click="declineCall">Từ chối</button>
            </div>
        </div>

        <div v-else class="video-call-container">
            <div class="call-info">
                <h3>@{{ callInfo.room.name }}</h3>
                <div class="participants-list">
                    @{{ getParticipantsCount() }} người tham gia
                </div>
            </div>

            <div class="video-grid">
                <div v-for="participant in participants"
                     :key="participant.id"
                     :class="['video-item', { own: participant.user_id === currentUser.id }]">
                    <video :ref="'video_' + participant.user_id" autoplay muted></video>
                    <div class="video-overlay">
                        @{{ participant.user.ho_va_ten }}
                        <span v-if="participant.is_muted">🔇</span>
                        <span v-if="participant.is_video_off">📷</span>
                    </div>
                </div>
            </div>

            <div class="controls">
                <button class="control-btn"
                        :class="{ muted: isMuted, secondary: !isMuted }"
                        @click="toggleMute">
                    <i class="fas fa-microphone" v-if="!isMuted"></i>
                    <i class="fas fa-microphone-slash" v-else></i>
                </button>

                <button class="control-btn"
                        :class="{ 'video-off': isVideoOff, secondary: !isVideoOff }"
                        @click="toggleVideo">
                    <i class="fas fa-video" v-if="!isVideoOff"></i>
                    <i class="fas fa-video-slash" v-else></i>
                </button>

                <button class="control-btn danger" @click="endCall">
                    <i class="fas fa-phone-slash"></i>
                </button>
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
                    callId: null,
                    callInfo: null,
                    participants: [],
                    loading: true,
                    error: null,
                    incomingCall: null,
                    isMuted: false,
                    isVideoOff: false,
                    localStream: null,
                    peerConnections: {},
                    pollingInterval: null
                }
            },
            async mounted() {
                // Lấy callId từ URL
                const urlParams = new URLSearchParams(window.location.search);
                this.callId = urlParams.get('callId');

                if (this.callId) {
                    await this.joinCall();
                } else {
                    this.error = 'Không tìm thấy thông tin cuộc gọi';
                    this.loading = false;
                }

                this.startPolling();
            },
            beforeUnmount() {
                this.stopPolling();
                this.cleanupMedia();
            },
            methods: {
                async joinCall() {
                    try {
                        const response = await axios.post(`/api/video-call/${this.callId}/join`);
                        this.callInfo = response.data.data;
                        this.participants = this.callInfo.participants.filter(p => p.status === 'joined');

                        await this.initializeMedia();
                        this.loading = false;

                    } catch (error) {
                        this.error = error.response?.data?.message || 'Không thể tham gia cuộc gọi';
                        this.loading = false;
                    }
                },

                async initializeMedia() {
                    try {
                        // Lấy media stream
                        this.localStream = await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: true
                        });

                        // Hiển thị local video
                        const localVideo = this.$refs[`video_${this.currentUser.id}`];
                        if (localVideo && localVideo[0]) {
                            localVideo[0].srcObject = this.localStream;
                        }

                        // Khởi tạo WebRTC connections với các participants khác
                        this.participants.forEach(participant => {
                            if (participant.user_id !== this.currentUser.id) {
                                this.createPeerConnection(participant.user_id);
                            }
                        });

                    } catch (error) {
                        console.error('Lỗi khởi tạo media:', error);
                        this.error = 'Không thể truy cập camera/microphone';
                    }
                },

                createPeerConnection(userId) {
                    const peerConnection = new RTCPeerConnection({
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' }
                        ]
                    });

                    // Thêm local stream
                    this.localStream.getTracks().forEach(track => {
                        peerConnection.addTrack(track, this.localStream);
                    });

                    // Xử lý ICE candidates
                    peerConnection.onicecandidate = (event) => {
                        if (event.candidate) {
                            // Gửi ICE candidate đến user khác
                            this.sendIceCandidate(userId, event.candidate);
                        }
                    };

                    // Xử lý remote stream
                    peerConnection.ontrack = (event) => {
                        const remoteVideo = this.$refs[`video_${userId}`];
                        if (remoteVideo && remoteVideo[0]) {
                            remoteVideo[0].srcObject = event.streams[0];
                        }
                    };

                    this.peerConnections[userId] = peerConnection;
                },

                async toggleMute() {
                    if (this.localStream) {
                        const audioTrack = this.localStream.getAudioTracks()[0];
                        if (audioTrack) {
                            audioTrack.enabled = !audioTrack.enabled;
                            this.isMuted = !audioTrack.enabled;

                            try {
                                await axios.post(`/api/video-call/${this.callId}/toggle-mute`);
                            } catch (error) {
                                console.error('Lỗi toggle mute:', error);
                            }
                        }
                    }
                },

                async toggleVideo() {
                    if (this.localStream) {
                        const videoTrack = this.localStream.getVideoTracks()[0];
                        if (videoTrack) {
                            videoTrack.enabled = !videoTrack.enabled;
                            this.isVideoOff = !videoTrack.enabled;

                            try {
                                await axios.post(`/api/video-call/${this.callId}/toggle-video`);
                            } catch (error) {
                                console.error('Lỗi toggle video:', error);
                            }
                        }
                    }
                },

                async endCall() {
                    try {
                        await axios.post(`/api/video-call/${this.callId}/leave`);
                        this.cleanupMedia();
                        window.close();
                    } catch (error) {
                        console.error('Lỗi kết thúc cuộc gọi:', error);
                    }
                },

                async acceptCall() {
                    this.incomingCall = null;
                    this.callId = this.incomingCall.call_id;
                    await this.joinCall();
                },

                async declineCall() {
                    try {
                        await axios.post(`/api/video-call/${this.incomingCall.call_id}/decline`);
                        this.incomingCall = null;
                        window.close();
                    } catch (error) {
                        console.error('Lỗi từ chối cuộc gọi:', error);
                    }
                },

                sendIceCandidate(userId, candidate) {
                    // Implement WebSocket để gửi ICE candidate
                    console.log('Gửi ICE candidate đến user:', userId, candidate);
                },

                getParticipantsCount() {
                    return this.participants.length;
                },

                cleanupMedia() {
                    if (this.localStream) {
                        this.localStream.getTracks().forEach(track => track.stop());
                    }

                    Object.values(this.peerConnections).forEach(pc => {
                        pc.close();
                    });

                    this.peerConnections = {};
                },

                startPolling() {
                    this.pollingInterval = setInterval(async () => {
                        if (this.callId) {
                            try {
                                const response = await axios.get(`/api/video-call/${this.callId}`);
                                this.callInfo = response.data.data;
                                this.participants = this.callInfo.participants.filter(p => p.status === 'joined');
                            } catch (error) {
                                console.error('Lỗi polling:', error);
                            }
                        }
                    }, 2000);
                },

                stopPolling() {
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
