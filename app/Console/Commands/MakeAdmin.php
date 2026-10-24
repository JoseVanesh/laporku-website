<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firestore\UserStore;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'firebase:make-admin {email : Email pengguna yang akan dijadikan admin}';

    protected $description = 'Mengubah peran pengguna di Firestore menjadi admin';

    public function handle(UserStore $users): int
    {
        $email = (string) $this->argument('email');

        $user = $users->findByEmail($email);

        if ($user === null) {
            $this->error("Pengguna dengan email {$email} belum terdaftar.");
            $this->line('Daftarkan dulu akunnya lewat halaman /register di website, lalu jalankan perintah ini lagi.');

            return self::FAILURE;
        }

        $users->update($user['uid'], ['role' => 'admin']);

        $this->info("Peran {$user['nama']} ({$email}) diubah menjadi admin.");
        $this->line('Agar berlaku, pengguna tersebut harus logout lalu login kembali.');

        return self::SUCCESS;
    }
}
