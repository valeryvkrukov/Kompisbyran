<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="fos_user")
 * @UniqueEntity(fields="email", message="Epostadressen är redan registrerad")
 **/
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $profilePicture;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="fluentSpeaker")
     */
    protected $fluentSpeakerConnections;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="learner")
     */
    protected $learnerConnections;

    /**
     * @var Connection[]
     *
     * @ORM\OneToMany(targetEntity="Connection", mappedBy="createdBy")
     */
    protected $createdConnections;

    /**
     * @var ConnectionRequest[]
     *
     * @ORM\OneToMany(targetEntity="ConnectionRequest", mappedBy="user")
     */
    protected $connectionRequests;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $wantToLearn = false;

    /**
     * @var GeneralCategory[]
     *
     * @Assert\Count(
     *     min=1,
     *     max=5,
     *     minMessage="Du måste välja minst ett intresse",
     *     maxMessage="Du kan inte välja fler än 5 intressen",
     *     groups={"settings"}
     * )
     * @ORM\ManyToMany(targetEntity="GeneralCategory", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_categories",
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $categories;

    /**
     * @var MusicCategory[]
     *
     * @ORM\ManyToMany(targetEntity="MusicCategory", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_music_categories",
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $musicCategories;

    /**
     * @var int
     *
     * @Assert\Range(min=18, max=100, minMessage="Du måste vara minst 18 år", groups={"settings"})
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $age;

    /**
     * @var string
     *
     * @Assert\NotNull(groups={"settings"})
     *
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    protected $gender;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", name="from_country", nullable=true)
     */
    protected $from;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $internalComment;

    /**
     * @var string
     *
     * Might be removed after music friend campaign
     * //Assert\NotBlank(groups={"settings"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $district;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hasChildren = false;

    /**
     * @var string
     * @Assert\Email
     */
    protected $email;

    /**
     * @var ConnectionComment[]
     *
     * @ORM\OneToMany(targetEntity="ConnectionComment", mappedBy="connection")
     */
    protected $comments;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $musicFriend = false;

    /**
     * @var Municipality
     *
     * @Assert\NotBlank(groups={"settings"})
     * @ORM\ManyToOne(targetEntity="Municipality", inversedBy="users")
     */
    protected $municipality;

    public function __construct()
    {
        $this->fluentSpeakerConnections = new ArrayCollection();
        $this->learnerConnections = new ArrayCollection();
        $this->connectionRequests = new ArrayCollection();
        $this->createdConnections = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->from = 'SE';
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return Connection[]|ArrayCollection
     */
    public function getConnections()
    {
        return new ArrayCollection(array_merge(
            $this->fluentSpeakerConnections->toArray(),
            $this->learnerConnections->toArray()
        ));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return trim(sprintf('%s %s', $this->getFirstName(), $this->getLastName()));
    }
    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $profilePicture
     */
    public function setProfilePicture($profilePicture)
    {
        $this->profilePicture = $profilePicture;
    }

    /**
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @param mixed $fluentSpeakerConnections
     */
    public function setFluentSpeakerConnections($fluentSpeakerConnections)
    {
        $this->fluentSpeakerConnections = $fluentSpeakerConnections;
    }

    /**
     * @return mixed
     */
    public function getFluentSpeakerConnections()
    {
        return $this->fluentSpeakerConnections;
    }

    /**
     * @param \AppBundle\Entity\Connection[] $learnerConnections
     */
    public function setLearnerConnections($learnerConnections)
    {
        $this->learnerConnections = $learnerConnections;
    }

    /**
     * @return \AppBundle\Entity\Connection[]
     */
    public function getLearnerConnections()
    {
        return $this->learnerConnections;
    }

    /**
     * @param \AppBundle\Entity\ConnectionRequest[] $connectionRequests
     */
    public function setConnectionRequests($connectionRequests)
    {
        $this->connectionRequests = $connectionRequests;
    }

    /**
     * @return \AppBundle\Entity\ConnectionRequest[]
     */
    public function getConnectionRequests()
    {
        return $this->connectionRequests;
    }

    /**
     * @param bool $wantToLearn
     */
    public function setWantToLearn($wantToLearn)
    {
        $this->wantToLearn = $wantToLearn;
    }

    /**
     * @return bool
     */
    public function getWantToLearn()
    {
        return $this->wantToLearn;
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * @param string $internalComment
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->username = $email;
    }

    /**
     * @param string $emailCanonical
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
        $this->usernameCanonical = $emailCanonical;
    }

    /**
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * @param bool $hasChildren
     */
    public function setHasChildren($hasChildren)
    {
        $this->hasChildren = $hasChildren;
    }

    /**
     * @return bool
     */
    public function isMusicFriend()
    {
        return $this->musicFriend;
    }

    /**
     * @param bool $musicFriend
     */
    public function setMusicFriend($musicFriend)
    {
        $this->musicFriend = $musicFriend;
    }

    /**
     * @return MusicCategory[]
     */
    public function getMusicCategories()
    {
        return $this->musicCategories;
    }

    /**
     * @param MusicCategory[] $musicCategories
     */
    public function setMusicCategories($musicCategories)
    {
        $this->musicCategories = $musicCategories;
    }

    /**
     * @return Municipality
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * @param Municipality $municipality
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;
    }
}
