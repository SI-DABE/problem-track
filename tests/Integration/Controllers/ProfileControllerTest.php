<?php

namespace Tests\Integration\Controllers;

use App\Models\User;
use Core\Constants\Constants;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ServerException;
use Tests\Integration\Controllers\ControllerTestCase;

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

    # To test work fine, you need to run the test with the following command:
    # sudo chown -R www-data:www-data public/assets/uploads
    # This will give the right permission to the upload directory
    public function test_update_avatar(): void
    {
        $cookieJar = new CookieJar();

        $client = new Client([
            'allow_redirects' => false, // Disable following redirects
            'base_uri' => 'http://web:8080'
        ]);

        // Login first
        $client->post('/login', [
            'form_params' => [
                'user[email]' => 'fulano@example.com',
                'user[password]' => '123456'
            ],
            'cookies' => $cookieJar
        ]);

        try {
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
        } catch (ServerException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            if (str_contains($responseBody, 'Permission denied')) {
                $this->markTestSkipped('Upload path is not writable by the web container.');
            }

            throw $e;
        }

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));

        $this->assertTrue(file_exists($this->avatarUploadPath));

        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        if (file_exists($this->avatarUploadPath)) {
            unlink($this->avatarUploadPath);
        }

        $userFolder = Constants::rootPath()->join('public/assets/uploads/users/' . $this->user->id);
        $this->removeDirectory($userFolder);
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
