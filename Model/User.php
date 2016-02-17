<?php

namespace WH\UserBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#use JMS\Serializer\Annotation\ExclusionPolicy;
#use JMS\Serializer\Annotation\Expose;
#use JMS\Serializer\Annotation\Groups;
#use JMS\Serializer\Annotation\VirtualProperty;

/**
 * User
 *
 * L'entity User se base sur User de FosUserBunde
 * Les modifications de cette class doivent être discutées
 * Sinon la faire hériter
 *
 * Todo : Town dans une base de ville
 *
 * Rq : L'email est déjà dans le FosUser Entity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="WH\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("email")
 *
 *
 */
class User extends BaseUser
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * Civilité
     *
     * @var string
     * @ORM\Column(name="civility", type="string", length=255, nullable=true)
     */
    protected $civility;

    /**
     * @var String
     * @ORM\Column(name="mobile", type="string", length=255, nullable=true)
     */
    protected $mobile;


    /**
     * @var String
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;


    /**
     * @var String
     * @ORM\Column(name="adress", type="string", length=255,nullable=true)
     */
    protected $adress;


    /**
     * Constructor
     */
    public function __construct()
    {

        parent::__construct();

        $this->states       = new ArrayCollection();
        $this->created      = new \Datetime();
        $this->password     = $this->generePassword();
        $this->enabled      = true;

        $this->addRole('ROLE_USER');

    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Juste avant de persister les données
     * @ORM\PrePersist
     */
    public function updateUserName()
    {

        $userName = $this->getEmail();

        $this->setUserName($userName);

    }

    /**
     * Génération de mot de passe
     * @param int $length
     * @return string
     */
    public function generePassword($length = 6) {

        $src = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '8', '9');

        $mot_de_passe = '';

        for($i = 0; $i < $length; $i++) {

            $mot_de_passe .= $src[rand(0, 30)];

        }

        return $mot_de_passe;

    }


    /**
     * @return string
     */
    public function getName() {

        if(empty($this->firstname)) {

            return $this->username;

        }else{

            return $this->firstname.' '.$this->lastname;
        }


    }




    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set civility
     *
     * @param string $civility
     * @return User
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Get civility
     *
     * @return string 
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return User
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get adress
     *
     * @return string 
     */
    public function getAdress()
    {
        return $this->adress;
    }
}
