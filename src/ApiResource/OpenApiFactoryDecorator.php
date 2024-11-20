<?php

namespace App\ApiResource;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model as OpenApiModel;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;

/**
 * @codeCoverageIgnore Does not need test coverage.
 */
class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new ArrayObject();
        $securitySchemes['access_user'] = new OpenApiModel\SecurityScheme(
            type: 'apiKey',
            description: '<i>Used together with Access-Token.</i>',
            name: 'Access-User',
            in: 'header'
        );
        $securitySchemes['access_token'] = new OpenApiModel\SecurityScheme(
            type: 'apiKey',
            description: '<i>Used together with Access-User.</i>',
            name: 'Access-Token',
            in: 'header'
        );
        $securitySchemes['authorization'] = new OpenApiModel\SecurityScheme(
            type: 'http',
            description: '<i>API Bearer token or JWT.</i>',
            name: 'Authorization',
            scheme: 'Bearer' // so we don't have to type "Bearer" each time we want to enter authorization
        );

        // In the OpenAPI docs (ReDoc) we want to see how the authentication types are grouped.
        // It will be displayed as: authorization or (access_user and access_token).
        $security = $openApi->getSecurity() ?: new ArrayObject();
        $security = [
            [
                'access_user' => [],
                'access_token' => [],
            ],
            [
                'authorization' => []
            ],
        ];
        $openApi = $openApi->withSecurity($security);

//        $pathItem = $openApi->getPaths()->getPath('/api/carriers');
//
//        $operation = $pathItem->getGet();
//        $openApi->getPaths()->addPath('/api/carriers', $pathItem->withGet(
//            $operation->withParameters(array_merge(
//                $operation->getParameters(),
//                [new OpenApiModel\Parameter('includeBase64', 'query', 'Set as 1 or true to also include the base64 encoded data of the logo')]
//            ))
//        ));
//
//        $pathItem = $openApi->getPaths()->getPath('/api/connections');
//
//        $operation = $pathItem->getGet();
//        $openApi->getPaths()->addPath('/api/connections', $pathItem->withGet(
//            $operation->withParameters(array_merge(
//                $operation->getParameters(),
//                [new OpenApiModel\Parameter('includeBase64', 'query', 'Set as 1 or true to also include the base64 encoded data of the logo')]
//            ))
//        ));

        return $openApi->withServers([]);
    }
}
