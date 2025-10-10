<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\ProfileAvatar;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    protected $usesDatabase = true;
    
    private ProfileAvatar $profileAvatar;
    private User $user;

    /** @var array<string, mixed> $image */
    private array $image;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->user->save();

        $tmpFile = tempnam(sys_get_temp_dir(), 'php');
        $this->image = [
            'name' => 'avatar_test.jpg',
            'full_path' => 'avatar_test.jpg',
            'type' => 'image/jpg',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => filesize($tmpFile),
        ];

        $this->profileAvatar = new ProfileAvatar(
            model: $this->user,
            validations: [
                'extension' => ['jpg', 'png'],
                'size' => 2 * 1024 * 1024, // 2MB
            ]
        );
    }

    public function test_upload(): void
    {
        $profileAvatar = $this->getMockBuilder(ProfileAvatar::class)
            ->setConstructorArgs([$this->user, [
                'extension' => ['jpg', 'png'],
                'size' => 2 * 1024 * 1024,
            ]])
            ->onlyMethods(['updateFile'])
            ->getMock();

        $profileAvatar->expects($this->once())
            ->method('updateFile')
            ->willReturn(true);

        $resp = $profileAvatar->update($this->image);
        $this->assertTrue($resp);

        $this->assertEquals($this->user->avatar_name, 'avatar.jpg');
    }

    public function test_update_avatar_invalid_extension(): void
    {
        $this->image['name'] = 'avatar.txt';
        $resp = $this->profileAvatar->update($this->image);

        $this->assertFalse($resp);
    }

    public function test_update_avatar_invalid_size(): void
    {
        $this->image['size'] = 3 * 1024 * 1024; // 3MB
        $resp = $this->profileAvatar->update($this->image);

        $this->assertFalse($resp);
    }
}
