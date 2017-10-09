<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09/10/17
 * Time: 10:36
 */


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="customer")
 * Representação da Entidade Customer
 */
class Customer
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
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=false)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="age", type="string", length=3, nullable=false)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=24, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="created_at", type="string",  nullable=false)
     */
    private $created_at;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_at", type="string",  nullable=false)
     */
    private $updated_at;

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
     * @return Customer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     *
     * @return Customer
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Customer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param string $age
     *
     * @return Customer
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     *
     * @return Customer
     */
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * @param string $updated_at
     *
     * @return Customer
     */
    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

}
