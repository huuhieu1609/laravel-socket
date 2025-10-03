<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KhachHang;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo users mẫu
        $users = [
            [
                'ho_va_ten' => 'Nguyễn Văn A',
                'email' => 'user1@example.com',
                'password' => Hash::make('password123'),
            ],
               [
                'ho_va_ten' => 'Trần Hữu Hiếu',
                'email' => 'huuhieutt12.1@gmail.com',
                'password' => Hash::make('password123'),
            ],
            [
                'ho_va_ten' => 'Trần Thị B',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'ho_va_ten' => 'Lê Văn C',
                'email' => 'user3@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'ho_va_ten' => 'Phạm Thị D',
                'email' => 'user4@example.com',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $userData) {
            KhachHang::create($userData);
        }

        // Tạo phòng chat mẫu
        $rooms = [
            [
                'name' => 'Phòng chung',
                'description' => 'Phòng chat chung cho tất cả mọi người',
                'type' => 'group',
            ],
            [
                'name' => 'Phòng riêng tư',
                'description' => 'Phòng chat riêng tư',
                'type' => 'private',
            ],
            [
                'name' => 'Phòng dự án',
                'description' => 'Thảo luận về dự án mới',
                'type' => 'group',
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::create($roomData);
        }

        // Thêm participants vào các phòng
        $allUsers = KhachHang::all();
        $allRooms = Room::all();

        // Phòng 1: Tất cả users
        foreach ($allUsers as $index => $user) {
            RoomParticipant::create([
                'room_id' => $allRooms[0]->id,
                'user_id' => $user->id,
                'role' => $index === 0 ? 'admin' : 'member',
            ]);
        }

        // Phòng 2: Chỉ 2 users đầu
        RoomParticipant::create([
            'room_id' => $allRooms[1]->id,
            'user_id' => $allUsers[0]->id,
            'role' => 'admin',
        ]);
        RoomParticipant::create([
            'room_id' => $allRooms[1]->id,
            'user_id' => $allUsers[1]->id,
            'role' => 'member',
        ]);

        // Phòng 3: 3 users đầu
        for ($i = 0; $i < 3; $i++) {
            RoomParticipant::create([
                'room_id' => $allRooms[2]->id,
                'user_id' => $allUsers[$i]->id,
                'role' => $i === 0 ? 'admin' : 'member',
            ]);
        }

        // Tạo tin nhắn mẫu
        $messages = [
            [
                'room_id' => $allRooms[0]->id,
                'sender_id' => $allUsers[0]->id,
                'content' => 'Chào mừng mọi người đến với phòng chat!',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[0]->id,
                'sender_id' => $allUsers[1]->id,
                'content' => 'Xin chào! Rất vui được gặp mọi người.',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[0]->id,
                'sender_id' => $allUsers[2]->id,
                'content' => 'Phòng chat này thật tuyệt!',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[1]->id,
                'sender_id' => $allUsers[0]->id,
                'content' => 'Đây là tin nhắn riêng tư.',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[1]->id,
                'sender_id' => $allUsers[1]->id,
                'content' => 'Tôi hiểu rồi!',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[2]->id,
                'sender_id' => $allUsers[0]->id,
                'content' => 'Chúng ta hãy bắt đầu dự án mới!',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[2]->id,
                'sender_id' => $allUsers[1]->id,
                'content' => 'Tôi đồng ý! Dự án này sẽ rất thú vị.',
                'type' => 'text',
            ],
            [
                'room_id' => $allRooms[2]->id,
                'sender_id' => $allUsers[2]->id,
                'content' => 'Tôi cũng rất hào hứng với dự án này!',
                'type' => 'text',
            ],
        ];

        foreach ($messages as $messageData) {
            Message::create($messageData);
        }

        echo "Đã tạo dữ liệu mẫu thành công!\n";
        echo "Tài khoản test:\n";
        echo "user1@example.com / password123\n";
        echo "user2@example.com / password123\n";
        echo "user3@example.com / password123\n";
        echo "user4@example.com / password123\n";
        echo "huuhieutt12.1@gmail.com / password123\n";
    }
}
