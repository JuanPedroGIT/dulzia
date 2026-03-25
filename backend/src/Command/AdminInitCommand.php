<?php
namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:admin:init', description: 'Create or reset the admin user')]
class AdminInitCommand extends Command
{
    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Admin setup');

        $username = $io->ask('Username', 'admin');
        $password = $io->askHidden('Password');

        if (!$username || !$password) {
            $io->error('Username and password are required.');
            return Command::FAILURE;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $existing = $this->connection->fetchOne('SELECT id FROM admin_user WHERE username = ?', [$username]);

        if ($existing) {
            $this->connection->executeStatement(
                'UPDATE admin_user SET password_hash = ? WHERE username = ?',
                [$hash, $username]
            );
            $io->success("Admin user '$username' updated.");
        } else {
            $this->connection->executeStatement(
                'INSERT INTO admin_user (username, password_hash) VALUES (?, ?)',
                [$username, $hash]
            );
            $io->success("Admin user '$username' created.");
        }

        return Command::SUCCESS;
    }
}
