# Chat Realtime - Laravel + Vue.js

Một ứng dụng nhắn tin realtime được xây dựng bằng Laravel và Vue.js với giao diện đẹp và chức năng đầy đủ.

## Tính năng

- ✅ Đăng ký và đăng nhập người dùng
- ✅ Tạo phòng chat (riêng tư/nhóm)
- ✅ Gửi và nhận tin nhắn realtime
- ✅ Hiển thị tin nhắn chưa đọc
- ✅ Đánh dấu tin nhắn đã đọc
- ✅ Quản lý thành viên trong phòng
- ✅ Giao diện responsive và đẹp mắt
- ✅ Polling để cập nhật tin nhắn realtime
- ✅ Voice/Video call với WebRTC
- ✅ Mute/Unmute microphone
- ✅ Tắt/Bật camera
- ✅ Quản lý cuộc gọi (join/leave/decline)
- ✅ Push notifications cho tin nhắn mới
- ✅ Push notifications cho cuộc gọi đến
- ✅ Quản lý notifications (đọc/xóa)
- ✅ FCM integration cho mobile
- ✅ Toast notifications realtime cho tin nhắn mới
- ✅ Thông báo cho tất cả users (kể cả không trong phòng)

## Cài đặt

### Yêu cầu hệ thống
- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Node.js (tùy chọn)

### Bước 1: Clone project
```bash
git clone <repository-url>
cd BE_REALTIME
```

### Bước 2: Cài đặt dependencies
```bash
composer install
```

### Bước 3: Cấu hình môi trường
```bash
cp .env.example .env
php artisan key:generate
```

Cập nhật file `.env` với thông tin database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chat_realtime
DB_USERNAME=root
DB_PASSWORD=
```

### Bước 4: Chạy migration và seeder
```bash
php artisan migrate
php artisan db:seed
```

### Bước 5: Chạy server
```bash
php artisan serve
```

Truy cập: http://localhost:8000

## Tài khoản test

Sau khi chạy seeder, bạn có thể sử dụng các tài khoản sau:

| Email | Mật khẩu | Tên |
|-------|----------|-----|
| user1@example.com | password123 | Nguyễn Văn A |
| user2@example.com | password123 | Trần Thị B |
| user3@example.com | password123 | Lê Văn C |
| user4@example.com | password123 | Phạm Thị D |

## Cấu trúc Database

### Bảng `khach_hangs`
- `id` - ID người dùng
- `ho_va_ten` - Họ và tên
- `email` - Email (unique)
- `password` - Mật khẩu (hashed)
- `created_at`, `updated_at` - Timestamps

### Bảng `rooms`
- `id` - ID phòng
- `name` - Tên phòng
- `description` - Mô tả phòng
- `type` - Loại phòng (private/group)
- `avatar` - Avatar phòng (tùy chọn)
- `created_at`, `updated_at` - Timestamps

### Bảng `room_participants`
- `id` - ID tham gia
- `room_id` - ID phòng (foreign key)
- `user_id` - ID người dùng (foreign key)
- `role` - Vai trò (admin/member)
- `joined_at` - Thời gian tham gia
- `left_at` - Thời gian rời phòng (nullable)
- `created_at`, `updated_at` - Timestamps

### Bảng `messages`
- `id` - ID tin nhắn
- `room_id` - ID phòng (foreign key)
- `sender_id` - ID người gửi (foreign key)
- `content` - Nội dung tin nhắn
- `type` - Loại tin nhắn (text/image/file)
- `file_path` - Đường dẫn file (nullable)
- `is_read` - Trạng thái đã đọc
- `read_at` - Thời gian đọc (nullable)
- `created_at`, `updated_at` - Timestamps

## API Endpoints

### Authentication
- `POST /api/register` - Đăng ký
- `POST /api/login` - Đăng nhập
- `POST /api/logout` - Đăng xuất
- `GET /api/me` - Lấy thông tin user

### Rooms
- `GET /api/rooms` - Lấy danh sách phòng
- `POST /api/rooms` - Tạo phòng mới
- `GET /api/rooms/{id}` - Lấy thông tin phòng
- `POST /api/rooms/{id}/participants` - Thêm thành viên
- `POST /api/rooms/{id}/leave` - Rời phòng

### Chat
- `GET /api/rooms/{roomId}/messages` - Lấy tin nhắn
- `POST /api/rooms/{roomId}/messages` - Gửi tin nhắn
- `POST /api/rooms/{roomId}/read` - Đánh dấu đã đọc
- `GET /api/unread-count` - Lấy số tin nhắn chưa đọc
- `DELETE /api/messages/{messageId}` - Xóa tin nhắn

### Video Call
- `POST /api/rooms/{roomId}/video-call/initiate` - Khởi tạo cuộc gọi
- `POST /api/video-call/{callId}/join` - Tham gia cuộc gọi
- `POST /api/video-call/{callId}/leave` - Rời cuộc gọi
- `POST /api/video-call/{callId}/decline` - Từ chối cuộc gọi
- `POST /api/video-call/{callId}/toggle-mute` - Tắt/Bật mic
- `POST /api/video-call/{callId}/toggle-video` - Tắt/Bật camera
- `GET /api/video-call/{callId}` - Lấy thông tin cuộc gọi
- `GET /api/rooms/{roomId}/video-call/active` - Lấy cuộc gọi đang hoạt động

### Notifications
- `GET /api/notifications` - Lấy danh sách notifications
- `GET /api/notifications/unread-count` - Lấy số notifications chưa đọc
- `POST /api/notifications/{id}/read` - Đánh dấu notification đã đọc
- `POST /api/notifications/mark-all-read` - Đánh dấu tất cả đã đọc
- `DELETE /api/notifications/{id}` - Xóa notification
- `POST /api/notifications/fcm-token` - Cập nhật FCM token
- `DELETE /api/notifications/fcm-token` - Xóa FCM token
- `POST /api/notifications/test` - Test push notification

### Realtime Features
- **Toast Notifications**: Hiển thị toast ngay lập tức khi có tin nhắn mới từ phòng khác
- **Global Notifications**: Gửi thông báo cho tất cả users trong hệ thống
- **Auto-polling**: Kiểm tra tin nhắn mới mỗi 2 giây
- **Click to Open**: Click toast để mở phòng chat tương ứng

## Cách sử dụng

1. **Đăng nhập**: Sử dụng email và mật khẩu để đăng nhập
2. **Tạo phòng**: Click "Tạo phòng mới" để tạo phòng chat
3. **Chọn phòng**: Click vào phòng trong sidebar để vào chat
4. **Gửi tin nhắn**: Nhập tin nhắn và nhấn Enter hoặc click nút gửi
5. **Quản lý phòng**: Admin có thể thêm thành viên vào phòng

## Tính năng Realtime

Hiện tại ứng dụng sử dụng **polling** để cập nhật tin nhắn realtime:
- Tự động cập nhật tin nhắn mỗi 3 giây
- Hiển thị số tin nhắn chưa đọc
- Tự động scroll xuống tin nhắn mới nhất

## Cải tiến có thể thực hiện

1. **WebSocket**: Thay thế polling bằng WebSocket để realtime hơn
2. **File upload**: Thêm chức năng gửi file và hình ảnh
3. **Emoji**: Thêm emoji picker
4. **Voice/Video call**: Thêm chức năng gọi video
5. **Push notification**: Thông báo khi có tin nhắn mới
6. **Message encryption**: Mã hóa tin nhắn
7. **Message search**: Tìm kiếm tin nhắn
8. **Message reactions**: Thêm reaction cho tin nhắn

## Công nghệ sử dụng

- **Backend**: Laravel 11, PHP 8.1+
- **Frontend**: Vue.js 3, Axios
- **Database**: MySQL/PostgreSQL
- **Styling**: CSS3, Font Awesome
- **Authentication**: Custom middleware

## License

MIT License
