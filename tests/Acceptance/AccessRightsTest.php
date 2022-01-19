<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserAdmin;
use App\DataFixtures\UserCommitter;
use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;
use App\DataFixtures\UserWatcher;
use App\Entity\User;

class AccessRightsTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    const LOGIN_URL = 'http://localhost/login';

    public function testAnonymousRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([UserWatcher::class]);

        $user = $this->getUser(UserWatcher::class, $referenceRepository);

        $this->assertAccessRights('/login', 200);
        $this->assertAccessRights('/loggedout', 200);

        $this->assertAccessRights('/history', 200);

        /* Redirect to index.md */
        $this->client->followRedirects(true);
        $this->assertAccessRights('/browse/', 200);
        $this->client->followRedirects(false);

        $this->assertAccessRights('/browse/?action=list', 200);
        $this->assertAccessRights('/browse/?action=file.upload');
        $this->assertAccessRights('/browse/?action=file.create&extension=txt');
        $this->assertAccessRights('/browse/?action=file.create&extension=md');
        $this->assertAccessRights('/browse/?action=subdirectory.create');
        $this->assertAccessRights('/browse/examples/?action=remove');

        $this->assertAccessRights('/browse/index.md', 200);
        $this->assertAccessRights('/browse/index.md?action=history', 200);
        $this->assertAccessRights('/browse/index.md?action=edit');
        $this->assertAccessRights('/browse/index.md?action=move');
        $this->assertAccessRights('/browse/index.md?action=remove');

        $this->assertAccessRights('/users/');
        $this->assertAccessRights(sprintf("/users/%s/edit", $user->id));
        $this->assertAccessRights(sprintf("/users/%s/delete", $user->id));
    }

    public function testWatcherRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([UserWatcher::class]);

        $user = $this->getUser(UserWatcher::class, $referenceRepository);

        $this->assertAccessRights('/history', 200, $user);

        $this->assertAccessRights('/browse/', 302, $user);
        $this->assertAccessRights('/browse/?action=list', 200, $user);
        $this->assertAccessRights('/browse/?action=file.upload', 403, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=txt', 403, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=md', 403, $user);
        $this->assertAccessRights('/browse/?action=subdirectory.create', 403, $user);
        $this->assertAccessRights('/browse/examples/?action=remove', 403, $user);

        $this->assertAccessRights('/browse/index.md', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=history', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=edit', 403, $user);
        $this->assertAccessRights('/browse/index.md?action=move', 403, $user);
        $this->assertAccessRights('/browse/index.md?action=remove', 403, $user);

        $this->assertAccessRights('/users/', 403, $user);
        $this->assertAccessRights(sprintf("/users/%s/edit", $user->id), 403, $user);
        $this->assertAccessRights(sprintf("/users/%s/delete", $user->id), 403, $user);
    }

    public function testCommitterRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([UserCommitter::class]);

        $user = $this->getUser(UserCommitter::class, $referenceRepository);

        $this->assertAccessRights('/history', 200, $user);

        $this->assertAccessRights('/browse/', 302, $user);
        $this->assertAccessRights('/browse/?action=list', 200, $user);
        $this->assertAccessRights('/browse/?action=file.upload', 200, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=txt', 200, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=md', 200, $user);
        $this->assertAccessRights('/browse/?action=subdirectory.create', 200, $user);
        $this->assertAccessRights('/browse/examples/?action=remove', 200, $user);

        $this->assertAccessRights('/browse/index.md', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=history', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=edit', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=move', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=remove', 302, $user);

        $this->assertAccessRights('/users/', 403, $user);
        $this->assertAccessRights(sprintf("/users/%s/edit", $user->id), 403, $user);
        $this->assertAccessRights(sprintf("/users/%s/delete", $user->id), 403, $user);
    }

    public function testAdminRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $user = $this->getUser(UserAdmin::class, $referenceRepository);

        $this->assertAccessRights('/history', 200, $user);

        $this->assertAccessRights('/browse/', 302, $user);
        $this->assertAccessRights('/browse/?action=list', 200, $user);
        $this->assertAccessRights('/browse/?action=file.upload', 200, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=txt', 200, $user);
        $this->assertAccessRights('/browse/?action=file.create&extension=md', 200, $user);
        $this->assertAccessRights('/browse/?action=subdirectory.create', 200, $user);
        $this->assertAccessRights('/browse/examples/?action=remove', 200, $user);

        $this->assertAccessRights('/browse/index.md', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=history', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=edit', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=move', 200, $user);
        $this->assertAccessRights('/browse/index.md?action=remove', 302, $user);

        $this->assertAccessRights('/users/', 200, $this->getUser(UserAdmin::class, $referenceRepository));
        $this->assertAccessRights(sprintf("/users/%s/edit", $user->id), 200, $user);
        $this->assertAccessRights(sprintf("/users/%s/delete", $user->id), 302, $user);
    }

    protected function assertAccessRights(
        string $url,
        ?int $expectedStatus = null,
        ?User $user = null,
    ): void {
        $this->logOut();
        if (null !== $user) {
            $this->logIn($user);
        }
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        if (500 === $statusCode) {
            echo $this->client->getResponse()->getContent();
            $this->fail(sprintf('Status code was 500 for %s', $url));
        }

        if (null === $expectedStatus) {
            $this->assertEquals(302, $statusCode);
            $this->assertEquals(self::LOGIN_URL, $response->headers->get('Location'));

            return;
        }

        $this->assertEquals(
            $expectedStatus,
            $statusCode,
            sprintf('%s [%s]', $url, $user !== null ? $user->getUsername() : null)
        );
    }
}
