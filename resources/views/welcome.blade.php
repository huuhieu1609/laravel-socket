<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Realtime - Laravel + Vue.js</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 800px;
            max-width: 800px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 35px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .form-container {
            padding: 50px 35px 35px 35px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .toggle-form {
            text-align: center;
            margin-top: 20px;
        }

        .toggle-form button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container">
           <div class="row">
            <div class="col-lg-3"></div>
            <div class="col-lg-6">
                 <div class="header">
                <h1>Chat Realtime</h1>
                <p>Laravel + Vue.js + WebSocket</p>
            </div>

            <div class="form-container">
                <div v-if="!isRegister">
                    <h2 style="margin-bottom: 20px; color: #333;">Đăng nhập</h2>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" v-model="loginForm.email" placeholder="Nhập email">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" v-model="loginForm.password" placeholder="Nhập mật khẩu">
                    </div>
                    <button class="btn btn-primary" @click="login" :disabled="loading">
                        @{{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
                    </button>
                    <div class="toggle-form">
                        <button @click="isRegister = true">Chưa có tài khoản? Đăng ký ngay</button>
                    </div>
                </div>
                <div v-else>
                    <h2 style="margin-bottom: 20px; color: #333;">Đăng ký</h2>
                    <div class="form-group">
                        <label>Họ và tên:</label>
                        <input type="text" v-model="registerForm.ho_va_ten" placeholder="Nhập họ và tên">
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" v-model="registerForm.email" placeholder="Nhập email">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" v-model="registerForm.password" placeholder="Nhập mật khẩu">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu:</label>
                        <input type="password" v-model="registerForm.password_confirmation" placeholder="Nhập lại mật khẩu">
                    </div>
                    <button class="btn btn-primary" @click="register" :disabled="loading">
                        @{{ loading ? 'Đang đăng ký...' : 'Đăng ký' }}
                    </button>
                    <div class="toggle-form">
                        <button @click="isRegister = false">Đã có tài khoản? Đăng nhập</button>
                    </div>
                </div>
                <div v-if="message" :class="messageType" style="margin-top: 15px;">
                    @{{ message }}
                </div>
            </div>
            </div>
           </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    isRegister: false,
                    loading: false,
                    message: '',
                    messageType: '',
                    loginForm: {
                        email: '',
                        password: ''
                    },
                    registerForm: {
                        ho_va_ten: '',
                        email: '',
                        password: '',
                        password_confirmation: ''
                    }
                }
            },
            methods: {
                async login() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const response = await axios.post('/api/login', this.loginForm);
                        this.message = response.data.message;
                        this.messageType = 'success';

                        // Lưu token
                        localStorage.setItem('token', response.data.data.token);
                        localStorage.setItem('user', JSON.stringify(response.data.data.user));

                        // Chuyển hướng đến trang chat
                        setTimeout(() => {
                            window.location.href = '/chat';
                        }, 1000);

                    } catch (error) {
                        this.message = error.response?.data?.message || 'Có lỗi xảy ra';
                        this.messageType = 'error';
                    } finally {
                        this.loading = false;
                    }
                },

                async register() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const response = await axios.post('/api/register', this.registerForm);
                        this.message = response.data.message;
                        this.messageType = 'success';

                        // Chuyển về form đăng nhập
                        setTimeout(() => {
                            this.isRegister = false;
                            this.message = '';
                        }, 2000);

                    } catch (error) {
                        this.message = error.response?.data?.message || 'Có lỗi xảy ra';
                        this.messageType = 'error';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
