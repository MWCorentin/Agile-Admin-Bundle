<?php

/*
 * This file is part of the Agile Admin Bundle Project.
 *
 * (c) Corentin Régnier <corentin.regnier59@gmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AgileAdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Builder
 */
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param FactoryInterface $factory
     *
     * @return ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, $options)
    {
        $menu = $factory->createItem('root');
        /** @var Request $request */
        $request   = $this->getRequest();
        $routeName = $request->get('_route');
        $this->addItem($menu, 'admin.nav.title', 'admin_homepage', 'home');

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     *
     * @return ItemInterface
     */
    public function breadcrumb(FactoryInterface $factory)
    {
        /** @var Request $request */
        $request   = $this->getRequest();
        $routeName = $request->get('_route');
        $menu      = $factory->createItem('root');

        return $menu;
    }

    /**
     * @param ItemInterface $menuItem
     * @param string        $label
     * @param string|null   $route
     * @param string|null   $icon
     * @param array         $routeParameters
     * @param string|null   $role
     *
     * @return ItemInterface|boolean
     */
    public function addItem(
        ItemInterface $menuItem,
        $label,
        $route = null,
        $icon = null,
        $routeParameters = [],
        $role = null
    ) {
        if ($role !== null && !$this->getAuthorization()->isGranted($role)) {
            return null;
        }

        if ($route === null) {
            $routeSetting = [];
        } else {
            $routeSetting = [
                'route' => $route,
            ];
        }

        if (!empty($routeParameters)) {
            $routeSetting['routeParameters'] = $routeParameters;
        }

        $item = $menuItem->addChild($this->getTranslator()->trans($label), $routeSetting);

        if (!empty($icon)) {
            $item->setAttribute('icon', 'fa fa-'.$icon);
        }

        if ($menuItem->isRoot() === true) {
            $item->setExtra('title', true);
        }

        return $item;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->container->get('translator');
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public function getAuthorization()
    {
        return $this->container->get('security.authorization_checker');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
