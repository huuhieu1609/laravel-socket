# Hướng dẫn nhanh - Chat Realtime

## Bước 1: Khởi động project

```bash
# Chạy server Laravel
php artisan serve
```

Truy cập: http://localhost:8000

## Bước 2: Đăng nhập test

Sử dụng một trong các tài khoản sau:

| Email | Mật khẩu |
|-------|----------|
| user1@example.com | password123 |
| user2@example.com | password123 |
| user3@example.com | password123 |
| user4@example.com | password123 |

## Bước 3: Test các tính năng

### 3.1. Xem danh sách phòng
- Sau khi đăng nhập, bạn sẽ thấy danh sách phòng trong sidebar
- Có sẵn 3 phòng: "Phòng chung", "Phòng riêng tư", "Phòng dự án"

### 3.2. Chat trong phòng
- Click vào một phòng để vào chat
- Gửi tin nhắn bằng cách nhập và nhấn Enter
- Tin nhắn sẽ hiển thị realtime (cập nhật mỗi 3 giây)

### 3.3. Tạo phòng mới
- Click "Tạo phòng mới" trong sidebar
- Điền thông tin phòng
- Chọn loại phòng (riêng tư/nhóm)

### 3.4. Test với nhiều user
- Mở nhiều tab trình duyệt
- Đăng nhập với các tài khoản khác nhau
- Chat trong cùng một phòng để test realtime

## Bước 4: Test API

### Đăng nhập và lấy token
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user1@example.com","password":"password123"}'
```

### Lấy danh sách phòng
```bash
curl -X GET http://localhost:8000/api/rooms \
  -H "Authorization: Bearer 1"
```

### Gửi tin nhắn
```bash
curl -X POST http://localhost:8000/api/rooms/1/messages \
  -H "Authorization: Bearer 1" \
  -H "Content-Type: application/json" \
  -d '{"content":"Xin chào mọi người!"}'
```

## Lưu ý

- Token = User ID (đơn giản hóa cho demo)
- Polling interval: 3 giây
- Database đã có sẵn dữ liệu mẫu
- Giao diện responsive, có thể test trên mobile

## Troubleshooting

### Lỗi database
```bash
php artisan migrate:fresh --seed
```

### Lỗi permission
```bash
chmod -R 755 storage bootstrap/cache
```

### Lỗi 500
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
``` 
