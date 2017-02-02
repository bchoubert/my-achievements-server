<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * File : A file and his metadata.
 *
 * @ORM\Table(name="files")
 *
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\FileRepository" )
 * @ORM\HasLifecycleCallbacks()
 */
class File
{

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=250, nullable=false)     *
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $description;

    /**
     * file used to upload the file to the server with a form.
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="Achievement", inversedBy="images")
     * @var Achievement
     */
    protected $achievement;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    public function defaultValues()
    {
        if (null != $this->getFile()) {
            $this->path = $this->createName($this->getFile(), $this->getFile()->getClientOriginalName());
            $this->description = $this->getDescription().' Original name : '.$this->getFile()->getClientOriginalName();
        }
    }

    public function createName(UploadedFile $file, $name)
    {
        $parts = explode(".", $name);

        $fileName = md5(uniqid()).'.'.$parts[count($parts) - 1];

        return $fileName;
    }

    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function upload(LifecycleEventArgs $event)
    {
        $this->getFile()->move($this->getUploadRootDir(), $this->path);
        $this->file = null;
    }

    /**
     * This function remove the physical file after the virtual file has been removed from the database
     * @ORM\PostRemove
     */
    public function removePhysicalFile(LifecycleEventArgs $event)
    {
        $file = $this->getAbsolutePath();

        if ($file) {
            $em = $event->getEntityManager();
            $repository = $em->getRepository(get_class($this));

            if ($repository->isNotUsed($this->getPath())) {
                unlink($file);
            }
        }
    }

    protected function getUploadRootDir()
    {
        // absolute path for the directory where uploaded file are saved.
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadDir()
    {
        // path for the directory where uploaded files are saved.
        return 'uploads/';
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    function getAchievement()
    {
        return $this->achievement;
    }

    function setAchievement(Achievement $achievement)
    {
        $this->achievement = $achievement;
    }
}
