<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09/10/17
 * Time: 14:58
 */


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="user")
 * Representação da Entidade Customer
 */
class User

{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="apikey", type="string", length=255, nullable=false)
     */
    private $apikey;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, nullable=false)
     */
    private $email;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string
     */
    public function getApikey()
    {
        return $this->id;
    }

    /**
     * @param string $apikey
     *
     * @return User
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $apikey
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function authenticate($apikey,$app)
    {
        $entityManager = $app['orm.em']; //$em
        $cuserRepository = $entityManager->getRepository('App\Entity\User');
        $user = $cuserRepository->findOneBy(['apikey' => $apikey]);
        if($user !== null){
            return $user->getId();
        }

        return false;
    }
}