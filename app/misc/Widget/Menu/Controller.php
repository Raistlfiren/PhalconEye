<?php

class Widget_Menu_Controller extends Widget_Controller
{

    public function indexAction()
    {
        $this->view->setVar('title', $this->getParam('title'));

        $menuId = $this->getParam('menu_id');
        $menu = null;
        if ($menuId)
            $menu = Menu::findFirst($menuId);
        if (!$menu)
            return $this->setNoRender();


        $menuClass = $this->getParam('class', 'int', 'nav');
        if (empty($menuClass))
            $menuClass = 'nav';

        $cacheKey = "menu_{$menuId}.cache";
        $navigation = $this->cacheData->get($cacheKey);

        if ($navigation === null) {

            $items = $this->_composeNavigation($menu->getMenuItem(array('parent_id IS NULL', 'order' => 'item_order ASC')));

            if (empty($items)) {
                return $this->setNoRender();
            }

            $navigation = new Navigation($this->di);
            $navigation
                ->setListClass($menuClass)
                ->setItems($items)
                ->setActiveItem($this->dispatcher->getActionName());

        }
        else{
            $navigation = unserialize($navigation);
        }

        $this->view->setVar('navigation', $navigation);
    }

    private function _composeNavigation($items)
    {
        $navigationItems = array();
        $index = 1;
        foreach ($items as $item) {
            $subItems = $item->getMenuItem(array('order' => 'item_order ASC'));
            $navigationItems[$index] = array(
                'title' => $item->getTitle()
            );

            if ($subItems->count() > 0) {
                $navigationItems[$index]['items'] = $this->_composeNavigation($subItems);
            } else {
                $navigationItems[$index]['href'] = $item->getHref();
            }
            $index++;
        }

        return $navigationItems;
    }

}