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
use \Symfony\Component\HttpFoundation\ParameterBag;

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

//CORS
$app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider(), [
//"cors.allowOrigin" => "http://petstore.swagger.wordnik.com",
]);

/**
 * Doctrine Informações Úteis
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html => bom para entendimento geral
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
 */

/**
 * Autenticação e Controles
 * Antes de executar qualquer requisição, verificar autenticação
 */
$app->before(function($request, $app) {

    if ($request->getMethod() == 'OPTIONS') {
        return new Response('', 204);
    }

    //Aceita Apenas JSON
    if($request->headers->get('Content-Type') != 'application/json'){
        $app->abort(400,$request->headers->get('Content-Type'));
    }

    //Prepara dados de request
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }

    //Autenticação
    TodoAuth::authenticate($request, $app);
});


$app->after(function (Request $request, Response $response, Application $app) {
    //Cors
    //Retorno os Header necessários para que o cliente(Browser), possa conseguir consultar a api
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type');
    $response->headers->set('Content-Type', 'application/json');//Header de resposta sempre application/json

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

})->assert('customer_id', '\d+');//Parametro deve ser um NÚMERO

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
 * raw {"name":"wilson","age":"11","gender":"m","phone":"(22)988515853"}
 */
$app->post('/customer', function(Request $request) use ($app) {

    //Exemplo de Validação
    $validacao = [];
    if(!$request->get('gender')){
        $validacao[] = ["Parâmetro Gender Obrigatório"];
    }
    if(strlen($request->get('gender')) > 1){
        $validacao[] = ["Parâmetro Gender não pode ultrapassar 1 caracteres"];
    }
    if($validacao){
        $payload = ['result' => $validacao];
        $code = 422;
        return $app->json($payload, $code);
    }

    //Atribuição dos valores post ao objeto $customer;
    $customer = new Customer();
    $customer->setName($request->get('name'));
    $customer->setAge($request->get('age'));
    $customer->setGender($request->get('gender'));
    $customer->setPhone($request->get('phone'));
    $customer->setCreated_at(date("Y-m-d H:i:s"));
    $customer->setUpdated_at(date("Y-m-d H:i:s"));

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
            'created_at'  => $customer->getCreated_at(),
            'updated_at'  => $customer->getUpdated_at()
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
 * PUT http://silex-api.br/customer/51
 * raw {"name":"wilson","age":"11","gender":"m","phone":"(22)988515853"}
 */

$app->put('/customer/{customer_id}', function($customer_id,Request $request) use ($app) {

    //return $app->json($request->request->all(), 200);
    $entityManager = $app['orm.em'];
    $customerRepository = $entityManager->getRepository('App\Entity\Customer');
    $customer = $customerRepository->find($customer_id);
    //return $app->json('delete', 200);
    //Erros
    if($customer === null){
        $payload = ['error' => ["Customer {$customer_id} não encontrado"]];
        return $app->json($payload, 400);
    } else {

        $customer->setName($request->get('name'));
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

$app["cors-enabled"]($app);



$app->run();
