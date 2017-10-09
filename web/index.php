<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09/10/17
 * Time: 08:16
 *
 * Autenticação: Headers: Authorization => Bearer d0763edaa9d9bd2a9516280e9044d885
 */


use Doctrine\Common\Annotations\AnnotationRegistry;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

use App\Middleware\Authentication as TodoAuth;

use \App\Entity\Customer;


$baseDir = __DIR__ . '/../';

$loader = require $baseDir . '/vendor/autoload.php';

$app = new Application();

//Centralizador de erros: Ex: Something goes terribly wrong: No route found for "POST /insertpost": Method Not Allowed (Allow: GET)
$app->error(function (Exception $e) use ($app) {

    return new \Symfony\Component\HttpFoundation\Response("Something goes terribly wrong: " . $e->getMessage());
});

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

//Configuração do Banco de dados => Doctrine Service Provide
$app->register(
    new DoctrineServiceProvider(),
    [
        'db.options' => [
            'driver'        => 'pdo_mysql',
            'host'          => 'localhost',
            'dbname'        => 'silex-doctrine',
            'user'          => 'root',
            'password'      => 'luizaroot',
            'charset'       => 'utf8',
            'driverOptions' => [
                1002 => 'SET NAMES utf8',
            ],
        ],
    ]
);

//Entidades através de annotations
$app->register(new DoctrineOrmServiceProvider(), [
    'orm.proxies_dir'             => $baseDir . 'src/App/Entity/Proxy',
    'orm.auto_generate_proxies'   => $app['debug'],
    'orm.em.options'              => [
        'mappings' => [
            [
                'type'                         => 'annotation',
                'namespace'                    => 'App\\Entity\\',
                'path'                         => $baseDir. 'src/App/Entity',
                'use_simple_annotation_reader' => false,
            ],
        ],
    ]
]);


/**
 * Doctrine Informações Úteis
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html => bom para entendimento geral
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
 */

/**
 * Autenticação
 * Antes de executar qualquer requisição, verificar autenticação
 * OAuth 2 => via HTTP Bearer Tokens.  OAuth2 protocolo
 */
$app->before(function($request, $app) {

    TodoAuth::authenticate($request, $app);
    //return 'oi';
});


/**
 * Retorna um customer pelo id
 * Ex: GET http://silex-api.br/customer/2
 */
$app->get('/customer/{customer_id}', function($customer_id) use ($app) {

    // Default entity manager.
    $entityManager = $app['orm.em']; //$em

    $customer = $entityManager->find('App\Entity\Customer', $customer_id);
    //$customer = $entityManager->findBy(['id' => 1]);
    if ($customer === null) {
        $payload = ['result' => false];
        $code = 201;
    } else {
        $result = [
          'name'   => $customer->getName(),
          'age'    => $customer->getAge(),
          'gender' => $customer->getGender(),
          'phone'  => $customer->getPhone(),
        ];
        $payload = ['result' => $result];
        $code = 200;
    }
    //echo '<pre>' . var_export($customer, true) . '</pre>';

    return $app->json($payload, $code);

});

/**
 * Retorna todos os Customers
 * Ex: GET http://silex-api.br/customers
 */
$app->get('/customers', function (Application $app) {

    // Default entity manager.
    $entityManager = $app['orm.em'];
    $customerRepository = $entityManager->getRepository('App\Entity\Customer');

    $customers = $customerRepository->findAll();
    if ($customers === null) {
        $payload = ['result' => false];
        $code = 201;
    } else {
        foreach ($customers as $customer ){
            $result[$customer->getId()] = [
                'name'   => $customer->getName(),
                'age'    => $customer->getAge(),
                'gender' => $customer->getGender(),
                'phone'  => $customer->getPhone(),
            ];
        }

        $payload = ['result' => $result];
        $code = 200;
    }

    return $app->json($payload, $code);

});

/**
 * Salvar novo Customer
 * Ex: POST http://silex-api.br/customer
 * EU Poderia implementar a lógica: Se customer existir, atualizar, se não, grava no banco um customer novo, mas isolei o atualziar no metodo put
 */
$app->post('/customer', function(Request $request) use ($app) {

    return $app->json($request->request->all(), 200);
    //return $app->json($request->get('id'), 200);
    //Validação
    //... 400 =>  	Bad Request

    //Atribuição dos valores post ao objeto $customer;
    $customer = new Customer();
    $customer->setName($request->get('name'));
    $customer->setAge($request->get('age'));
    $customer->setGender($request->get('gender'));
    $customer->setPhone($request->get('phone'));

    //Doctrine
    $entityManager = $app['orm.em'];
    $entityManager->persist($customer);
    $entityManager->flush();

    if(!$customer->getId()){
        //return $app->json($customer->getId(), 200);
        $payload = ['result' => false];
        $code = 500;
    } else {
        //return $app->json($customer->getId(), 200);
        $result = [
            'id' =>     $customer->getId(),
            'name'   => $customer->getName(),
            'age'    => $customer->getAge(),
            'gender' => $customer->getGender(),
            'phone'  => $customer->getPhone(),
        ];
        $payload = ['result' => $result];
        $code = 201;
    }

    return $app->json($payload, $code);

});

/**
 * Deleta um Customer
 * Ex: DELETE http://silex-api.br/customer/35
 */
$app->delete('/customer/{customer_id}', function($customer_id) use ($app) {

    //return $app->json('delete', 200);

    $entityManager = $app['orm.em'];
    $customerRepository = $entityManager->getRepository('App\Entity\Customer');
    $customer = $customerRepository->find($customer_id);
    //return $app->json('delete', 200);
    //Erros
    if($customer === null){
        $payload = ['error' => ["Customer {$customer_id} não encontrado"]];
        return $app->json($payload, 400);
    }

    $entityManager->remove($customer);
    $entityManager->flush();


    $payload = ['result' => "Customer {$customer_id} deletado com sucesso!"];
    return $app->json($payload, 204);

});

/**
 * Atualiza um Customer
 */
$app->put('/customer/{customer_id}', function($customer_id) use ($app) {

    //return $app->json($request->request->all(), 200);

    $data = json_decode($app['request']->getContent(), true);
    return $app->json($data, 200);

    $entityManager = $app['orm.em'];
    $customerRepository = $entityManager->getRepository('App\Entity\Customer');
    $customer = $customerRepository->find($customer_id);
    //return $app->json('delete', 200);
    //Erros
    if($customer === null){
        $payload = ['error' => ["Customer {$customer_id} não encontrado"]];
        return $app->json($payload, 400);
    } else {

        $customer->setName($request->attributes->get('name'));
        $customer->setAge($request->get('age'));
        $customer->setGender($request->get('gender'));
        $customer->setPhone($request->get('phone'));

        $entityManager->persist($customer);
        $entityManager->flush();

        $result = [
            'id' =>     $customer->getId(),
            'name'   => $customer->getName(),
            'age'    => $customer->getAge(),
            'gender' => $customer->getGender(),
            'phone'  => $customer->getPhone(),
        ];
        $payload = ['result' => $result];
        return $app->json($payload, 200);
    }

});





$app->run();
