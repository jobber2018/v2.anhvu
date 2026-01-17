<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 11:12 AM
 *
 */


namespace Users\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity
 * @orm\Table(name="privileges")
 */

class Privileges
{

    public function __construct() {
        $this->roles = new ArrayCollection();
        $this->userPrivileges = new ArrayCollection();
    }
    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;


    /** @orm\Column(type="string", name="controller") */
    private $controller;

    /** @orm\Column(type="string", name="action") */
    private $action;

    /** @orm\Column(type="string", name="allow") */
    private $allow;


    /** @orm\Column(type="integer", name="parent") */
    private $parent;

    /** @orm\Column(type="string", name="url") */
    private $url;

    /** @orm\Column(type="string", name="icon") */
    private $icon;

    /** @orm\Column(type="integer", name="menu_display") */
    private $menu_display;

    /** @orm\Column(type="integer", name="dashboard_display") */
    private $dashboard_display;

    /** @orm\Column(type="integer", name="menu_sort") */
    private $menu_sort;

    /**
     * Many Groups have Many Roles.
     * @orm\ManyToMany(targetEntity="Users\Entity\Roles", mappedBy="privileges")
     */
    private $roles;

    /**
     * Many Groups have Many Roles.
     * @orm\ManyToMany(targetEntity="Users\Entity\User", mappedBy="privatePrivileges")
     */
    private $userPrivileges;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAllow()
    {
        return $this->allow;
    }

    /**
     * @param mixed $allow
     */
    public function setAllow($allow)
    {
        $this->allow = $allow;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        if($roles!==null)
            $this->roles->add($roles);
        $this->roles = $roles;
    }

    public function addRoles(Roles $roles)
    {
        if (!$this->roles->contains($roles)) {
            $this->roles->add($roles);
            $roles->addPrivileges($this);
        }
        return $this;
    }

    public function removeRoles(Roles $roles){
        if ($this->roles->contains($roles)) {
            $this->roles->removeElement($roles);
            $roles->removePrivileges($this);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getMenuDisplay()
    {
        return $this->menu_display;
    }

    /**
     * @param mixed $menu_display
     */
    public function setMenuDisplay($menu_display): void
    {
        $this->menu_display = $menu_display;
    }

    /**
     * @return mixed
     */
    public function getDashboardDisplay()
    {
        return $this->dashboard_display;
    }

    /**
     * @param mixed $dashboard_display
     */
    public function setDashboardDisplay($dashboard_display): void
    {
        $this->dashboard_display = $dashboard_display;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }

    public function addUserPrivilege(User $user)
    {
        if (!$this->userPrivileges->contains($user)) {
            $this->userPrivileges->add($user);
            $user->addPrivatePrivileges($this);
        }
        return $this;
    }

    public function removeUserPrivilege(User $user){
        if ($this->userPrivileges->contains($user)) {
            $this->userPrivileges->removeElement($user);
            $user->removePrivatePrivileges($this);
        }
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserPrivileges()
    {
        return $this->userPrivileges;
    }

    /**
     * @param ArrayCollection $userPrivileges
     */
    public function setUserPrivileges($userPrivileges)
    {
        $this->userPrivileges = $userPrivileges;
    }

    /**
     * @return mixed
     */
    public function getMenuSort()
    {
        return ($this->menu_sort?$this->menu_sort:0);
    }

    /**
     * @param mixed $menu_sort
     */
    public function setMenuSort($menu_sort)
    {
        $this->menu_sort = $menu_sort;
    }

}