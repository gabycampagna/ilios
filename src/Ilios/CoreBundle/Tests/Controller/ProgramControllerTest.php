<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * Program controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class ProgramControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        return [
            'Ilios\CoreBundle\Tests\Fixture\LoadProgramData',
            'Ilios\CoreBundle\Tests\Fixture\LoadPublishEventData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSchoolData',
            'Ilios\CoreBundle\Tests\Fixture\LoadProgramYearData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCurriculumInventoryReportData'
        ];
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [
        ];
    }

    public function testGetProgram()
    {
        $program = $this->container
            ->get('ilioscore.dataloader.program')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_programs',
                ['id' => $program['id']]
            )
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($program),
            json_decode($response->getContent(), true)['programs'][0]
        );
    }

    public function testGetAllPrograms()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_programs'));
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.program')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['programs']
        );
    }

    public function testPostProgram()
    {
        $data = $this->container->get('ilioscore.dataloader.program')
            ->create();
        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_programs'),
            json_encode(['program' => $data])
        );

        $response = $this->client->getResponse();
        $headers  = [];

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode());
        $this->assertTrue(
            $response->headers->contains(
                'Location'
            ),
            print_r($response->headers, true)
        );
    }

    public function testPostBadProgram()
    {
        $invalidProgram = $this->container
            ->get('ilioscore.dataloader.program')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_programs'),
            json_encode(['program' => $invalidProgram])
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), Codes::HTTP_BAD_REQUEST);
    }

    public function testPutProgram()
    {
        $program = $this->container
            ->get('ilioscore.dataloader.program')
            ->getOne()
        ;

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_programs',
                ['id' => $program['id']]
            ),
            json_encode(['program' => $program])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($program),
            json_decode($response->getContent(), true)['program']
        );
    }

    public function testDeleteProgram()
    {
        $program = $this->container
            ->get('ilioscore.dataloader.program')
            ->getOne()
        ;

        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_programs',
                ['id' => $program['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->client->request(
            'GET',
            $this->getUrl(
                'get_programs',
                ['id' => $program['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testProgramNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_programs', ['id' => '0'])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
