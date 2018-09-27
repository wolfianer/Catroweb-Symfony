<?php

namespace Catrobat\AppBundle\Controller\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="PocketCode",
 *         description="This is a simple discription.",
 *         termsOfService="https://share.catrob.at/pocketcode/termsOfUse",
 *         @OA\Contact(
 *             email="webmaster@catrobat.org"
 *         ),
 *         @OA\License(
 *             name="Licensename",
 *             url="licensurl"
 *         )
 *     ),
 *     @OA\Server(
 *         description="PockedCode API HOST",
 *         url="https://share.catrob.at/pocketcode/"
 *     ),
 *     @OA\ExternalDocumentation(
 *         description="Find out more about PocketCode",
 *         url="https://share.catrob.at/"
 *     )
 * )
 */
class SwaggerDocumentation
{

}