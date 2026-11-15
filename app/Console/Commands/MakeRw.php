<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firestore\UserStore;
use Illuminate\Console\Command;

class MakeRw extends Command
{
    protected $signature = 'firebase:make-rw {email : Email pengguna yang akan dijadikan RW}';

    protected $description = 'Mengubah peran pengguna di Firestore menjadi rw';

    public function handle(UserStore $users): int
    {
        $email = (string) $this->argument('email');

        $user = $users->findByEmail($email);

        if ($user === null) {
            $this->error("Pengguna dengan email {$email} belum terdaftar.");
            $this->line('Daftarkan dulu akunnya lewat halaman /register di website, lalu jalankan perintah ini lagi.');

            return self::FAILURE;
        }

        $users->update($user['uid'], ['role' => 'rw']);

        $this->info("Peran {$user['nama']} ({$email}) diubah menjadi rw.");
        $this->line('Agar berlaku, pengguna tersebut harus logout lalu login kembali.');

        return self::SUCCESS;
    }
}
