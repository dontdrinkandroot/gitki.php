<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;
use App\Entity\User;
use Symfony\Component\BrowserKit\AbstractBrowser;

class AccessRightsTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    public function testAnonymousRights()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights($client, '/login', 200);
        $this->assertAccessRights($client, '/loggedout', 200);
        $this->assertAccessRights($client, '/user/profile/');
        $this->assertAccessRights($client, '/user/profile/edit');

        $this->assertAccessRights($client, '/history');

        $this->assertAccessRights($client, '/browse/');
        $this->assertAccessRights($client, '/browse/?action=list');
        $this->assertAccessRights($client, '/browse/?action=file.upload');
        $this->assertAccessRights($client, '/browse/?action=file.create&extension=txt');
        $this->assertAccessRights($client, '/browse/?action=file.create&extension=md');
        $this->assertAccessRights($client, '/browse/?action=subdirectory.create');
        $this->assertAccessRights($client, '/browse/examples/?action=remove');

        $this->assertAccessRights($client, '/browse/index.md');
        $this->assertAccessRights($client, '/browse/index.md?action=history');
        $this->assertAccessRights($client, '/browse/index.md?action=edit');
        $this->assertAccessRights($client, '/browse/index.md?action=move');
        $this->assertAccessRights($client, '/browse/index.md?action=remove');

        $this->assertAccessRights($client, '/users/');
        $this->assertAccessRights($client, '/users/' . $user->getId() . '/edit');
        $this->assertAccessRights($client, '/users/' . $user->getId() . '/delete');
    }

    public function testWatcherRights()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights($client, '/user/profile/', 200, $this->getUser(Users::WATCHER, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/user/profile/edit',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );

        $this->assertAccessRights($client, '/history', 200, $this->getUser(Users::WATCHER, $referenceRepository));

        $this->assertAccessRights($client, '/browse/', 302, $this->getUser(Users::WATCHER, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/browse/?action=list',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.upload',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=txt',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=md',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=subdirectory.create',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/examples/?action=remove',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );

        $this->assertAccessRights(
            $client,
            '/browse/index.md',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=edit',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=move',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=remove',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );

        $this->assertAccessRights($client, '/users/', null, $this->getUser(Users::WATCHER, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/edit',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/delete',
            null,
            $this->getUser(Users::WATCHER, $referenceRepository)
        );
    }

    public function testCommitterRights()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights($client, '/history', 200, $this->getUser(Users::COMMITTER, $referenceRepository));

        $this->assertAccessRights($client, '/browse/', 302, $this->getUser(Users::COMMITTER, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/browse/?action=list',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.upload',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=txt',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=md',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=subdirectory.create',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/examples/?action=remove',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );

        $this->assertAccessRights(
            $client,
            '/browse/index.md',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=edit',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=move',
            200,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=remove',
            302,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );

        $this->assertAccessRights($client, '/users/', null, $this->getUser(Users::COMMITTER, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/edit',
            null,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/delete',
            null,
            $this->getUser(Users::COMMITTER, $referenceRepository)
        );
    }

    public function testAdminRights()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $user = $this->getUser(Users::COMMITTER, $referenceRepository);

        $this->assertAccessRights($client, '/history', 200, $this->getUser(Users::ADMIN, $referenceRepository));

        $this->assertAccessRights($client, '/browse/', 302, $this->getUser(Users::ADMIN, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/browse/?action=list',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.upload',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=txt',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=file.create&extension=md',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/?action=subdirectory.create',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/examples/?action=remove',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );

        $this->assertAccessRights($client, '/browse/index.md', 200, $this->getUser(Users::ADMIN, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=history',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=edit',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=move',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/browse/index.md?action=remove',
            302,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );

        $this->assertAccessRights($client, '/users/', 200, $this->getUser(Users::ADMIN, $referenceRepository));
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/edit',
            200,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
        $this->assertAccessRights(
            $client,
            '/users/' . $user->getId() . '/delete',
            302,
            $this->getUser(Users::ADMIN, $referenceRepository)
        );
    }

    /**
     * @param string $url            The url to test.
     * @param null   $expectedStatus The expected status code. Null if login is expected.
     * @param User   $user           The user to test or null for anonymous.
     */
    protected function assertAccessRights(AbstractBrowser $client, $url, $expectedStatus = null, User $user = null)
    {
        $this->logOut($client);
        if (null !== $user) {
            $this->logIn($client, $user);
        }
        $client->request('GET', $url);
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();

        if (500 === $statusCode) {
            echo $client->getResponse()->getContent();
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
