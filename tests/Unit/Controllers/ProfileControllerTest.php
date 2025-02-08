<?php

namespace Tests\Unit\Controllers;

use App\Models\User;
use Core\Constants\Constants;
use GuzzleHttp\Client;
use Symfony\Component\Finder\Finder;

class ProfileControllerTest extends ControllerTestCase
{
    private User $user;
    private string $avatarPath;
    private string $avatarUploadPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->createUser();
        $this->avatarPath = Constants::rootPath()->join('tests/files/avatar_test.jpg');
        $this->avatarUploadPath = Constants::rootPath()->join('public/assets/uploads/users/' . $this->user->id . '/avatar.jpg');
    }

    private function createUser(): void
    {
        $this->user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();
        $_SESSION['user']['id'] = $this->user->id;
    }

    /** TEST methods */
    public function test_show_current_user_profile(): void
    {
        $response = $this->get(action: 'show', controllerName: 'App\Controllers\ProfileController');

        $this->assertMatchesRegularExpression("/{$this->user->name}/", $response);
        $this->assertMatchesRegularExpression("/{$this->user->email}/", $response);
    }

    public function test_update_avatar(): void
    {
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar();

        $client = new Client([
            'allow_redirects' => false, // Disable following redirects
            'base_uri' => 'http://web:8080'
        ]);

        // Login first
        $resp = $client->post('/login', [
            'form_params' => [
                'user[email]' => 'fulano@example.com',
                'user[password]' => '123456'
            ],
            'cookies' => $cookieJar
        ]);

        $response = $client->post('/profile/avatar', [
            'multipart' => [
                [
                    'name' => 'user_avatar',
                    'contents' => fopen($this->avatarPath, 'r'),
                    'filename' => basename($this->avatarPath)
                ]
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));

        $this->assertTrue(file_exists($this->avatarUploadPath));

        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        unlink($this->avatarUploadPath);
        $usersFolder = Constants::rootPath()->join('public/assets/uploads/users');
        $this->removeDirectory($usersFolder);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        // Get all files and subdirectories inside the directory
        $items = array_diff(scandir($dir), array('.', '..')); // Exclude '.' and '..'

        foreach ($items as $item) {
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;

            // If it's a directory, call the function recursively
            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath); // Recursively remove subdirectory
            } else {
                // If it's a file, delete it
                unlink($itemPath);
            }
        }

        // Once all files and subdirectories are deleted, remove the main directory
        rmdir($dir);
    }
}
