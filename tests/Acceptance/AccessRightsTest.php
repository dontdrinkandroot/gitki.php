<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserAdmin;
use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;
use App\Entity\User;

class AccessRightsTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    public function testAnonymousRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights('/login', 200);
        $this->assertAccessRights('/loggedout', 200);
//        $this->assertAccessRights('/user/profile/');
//        $this->assertAccessRights('/user/profile/edit');

        $this->assertAccessRights('/history');

        $this->assertAccessRights('/browse/');
        $this->assertAccessRights('/browse/?action=list');
        $this->assertAccessRights('/browse/?action=file.upload');
        $this->assertAccessRights('/browse/?action=file.create&extension=txt');
        $this->assertAccessRights('/browse/?action=file.create&extension=md');
        $this->assertAccessRights('/browse/?action=subdirectory.create');
        $this->assertAccessRights('/browse/examples/?action=remove');

        $this->assertAccessRights('/browse/index.md');
        $this->assertAccessRights('/browse/index.md?action=history');
        $this->assertAccessRights('/browse/index.md?action=edit');
        $this->assertAccessRights('/browse/index.md?action=move');
        $this->assertAccessRights('/browse/index.md?action=remove');

        $this->assertAccessRights('/users/');
        $this->assertAccessRights('/users/' . $user->id . '/edit');
        $this->assertAccessRights('/users/' . $user->id . '/delete');
    }

    public function testWatcherRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

//        $this->assertAccessRights('/user/profile/', 200, $this->getUser(Users::WATCHER, $referenceRepository));
//        $this->assertAccessRights(
//            '/user/profile/edit',
//            200,
//            $this->getUser(Users::WATCHER, $referenceRepository)
//        );

        $this->assertAccessRights('/history', 200, $this->getUser(Users::WATCHER, $referenceRepository));

        $this->assertAccessRights('/browse/', 302, $this->getUser(Users::WATCHER, $referenceRepository));
        $this->assertAccessRights(
            '/browse/?action=list',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.upload',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=txt',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=md',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=subdirectory.create',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/examples/?action=remove',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );

        $this->assertAccessRights(
            '/browse/index.md',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=edit',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=move',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=remove',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );

        $this->assertAccessRights('/users/', null, $this->getUser(Users::WATCHER, $referenceRepository));
        $this->assertAccessRights(
            '/users/' . $user->id . '/edit',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/users/' . $user->id . '/delete',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
    }

    public function testCommitterRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights('/history', 200, $this->getUser(Users::COMMITTER, $referenceRepository));

        $this->assertAccessRights('/browse/', 302, $this->getUser(Users::COMMITTER, $referenceRepository));
        $this->assertAccessRights(
            '/browse/?action=list',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.upload',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=txt',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=md',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=subdirectory.create',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/examples/?action=remove',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );

        $this->assertAccessRights(
            '/browse/index.md',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=edit',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=move',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=remove',
            302,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );

        $this->assertAccessRights('/users/', null, $this->getUser(Users::COMMITTER, $referenceRepository));
        $this->assertAccessRights(
            '/users/' . $user->id . '/edit',
            null,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            '/users/' . $user->id . '/delete',
            null,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
    }

    public function testAdminRights(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights('/history', 200, $this->getUser(Users::ADMIN, $referenceRepository));

        $this->assertAccessRights('/browse/', 302, $this->getUser(Users::ADMIN, $referenceRepository));
        $this->assertAccessRights(
            '/browse/?action=list',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.upload',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=txt',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=file.create&extension=md',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/?action=subdirectory.create',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/examples/?action=remove',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );

        $this->assertAccessRights('/browse/index.md', 200, $this->getUser(Users::ADMIN, $referenceRepository));
        $this->assertAccessRights(
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=edit',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=move',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/browse/index.md?action=remove',
            302,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );

        $this->assertAccessRights('/users/', 200, $this->getUser(UserAdmin::class, $referenceRepository));
        $this->assertAccessRights(
            '/users/' . $user->id . '/edit',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            '/users/' . $user->id . '/delete',
            302,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
    }

    protected function assertAccessRights(string $url, ?int $expectedStatus = null, ?User $user = null): void
    {
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
            $this->assertEquals(302, $statusCode, sprintf('%s: Login expected', $url));
            $this->assertEquals('http://localhost/login', $response->headers->get('Location'));

            return;
        }

        $this->assertEquals(
            $expectedStatus,
            $statusCode,
            sprintf('%s [%s]', $url, $user !== null ? $user->getUsername() : null)
        );
    }
}
