<?php

namespace Ilios\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ilios\CoreBundle\Traits\MeshDescriptorsEntity;
use Ilios\CoreBundle\Traits\SessionConsolidationEntity;
use Ilios\CoreBundle\Traits\SortableEntity;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ilios\CoreBundle\Traits\IdentifiableEntity;
use Ilios\CoreBundle\Traits\StringableIdEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SessionLearningMaterial
 * @package Ilios\CoreBundle\Entity
 *
 * @ORM\Table(name="session_learning_material", indexes={
 *   @ORM\Index(name="session_lm_k", columns={"session_id", "learning_material_id"}),
 *   @ORM\Index(name="learning_material_id_k", columns={"learning_material_id"}),
 *   @ORM\Index(name="IDX_9BE2AF8D613FECDF", columns={"session_id"})
 * })
 * @ORM\Entity
 *
 * @JMS\ExclusionPolicy("all")
 * @JMS\AccessType("public_method")
 */
class SessionLearningMaterial implements SessionLearningMaterialInterface
{
    use IdentifiableEntity;
    use StringableIdEntity;
    use SessionConsolidationEntity;
    use MeshDescriptorsEntity;
    use SortableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_learning_material_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Assert\Type(type="integer")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 65000
     * )
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $notes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="required", type="boolean")
     *
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    protected $required;

    /**
     * @var boolean
     *
     * @ORM\Column(name="notes_are_public", type="boolean")
     *
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("publicNotes")
     */
    protected $publicNotes;

    /**
     * @var SessionInterface
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Session", inversedBy="learningMaterials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id", onDelete="CASCADE")
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $session;

    /**
     * @var LearningMaterialInterface
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="LearningMaterial", inversedBy="sessionLearningMaterials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="learning_material_id", referencedColumnName="learning_material_id", nullable=false)
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("learningMaterial")
     */
    protected $learningMaterial;

    /**
     * @var MeshDescriptorInterface
     *
     * @ORM\ManyToMany(targetEntity="MeshDescriptor", inversedBy="sessionLearningMaterials")
     * @ORM\JoinTable(name="session_learning_material_x_mesh",
     *   joinColumns={
     *     @ORM\JoinColumn(
     *       name="session_learning_material_id",
     *       referencedColumnName="session_learning_material_id",
     *       onDelete="CASCADE"
     *     )
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="mesh_descriptor_uid", referencedColumnName="mesh_descriptor_uid", onDelete="CASCADE")
     *   }
     * )
     *
     * @JMS\Expose
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("meshDescriptors")
     */
    protected $meshDescriptors;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     *
     * @ORM\Column(name="position", type="integer")
     *
     * @JMS\Expose
     * @JMS\SerializedName("position")
     * @JMS\Type("integer")
     */
    protected $position;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->meshDescriptors = new ArrayCollection();
        $this->publicNotes = false;
        $this->position = 0;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param boolean $publicNotes
     */
    public function setPublicNotes($publicNotes)
    {
        $this->publicNotes = $publicNotes;
    }

    /**
     * @return boolean
     */
    public function hasPublicNotes()
    {
        return $this->publicNotes;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param LearningMaterialInterface $learningMaterial
     */
    public function setLearningMaterial(LearningMaterialInterface $learningMaterial)
    {
        $this->learningMaterial = $learningMaterial;
    }

    /**
     * @return LearningMaterialInterface
     */
    public function getLearningMaterial()
    {
        return $this->learningMaterial;
    }
}
