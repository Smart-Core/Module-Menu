<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\EngineBundle\Module\Controller;
use SmartCore\Bundle\EngineBundle\Response;

class MenuController extends Controller
{
    protected $menu_group_id = 'A';
    protected $max_depth = 0;
    protected $css_class = '';
    protected $tpl = '';

    protected $_tree_level = 0;
    protected $_folder_tree_list_arr = array();
//    protected $tree_link = array();
    
    protected $only_is_active = true;
    protected $selected_inheritance = true;

    /**
     * Конструктор.
     */
    protected function init()
    {
        $this->View->setOptions(array(
            'bundle' => 'MenuModule::',
        ));
    }

    /**
     * Запуск модуля.
     */
    public function indexAction()
    {
        if (!empty($this->tpl)) {
            $this->View->setTemplateName($this->tpl);
        }

        $this->_getTreeList($this->getTree(0, $this->max_depth));
        $this->View->css_class = $this->css_class;
        $this->View->items = $this->_folder_tree_list_arr;
        
        return new Response($this->View);
    }

    /**
     * Получить параметры кеширования модуля.
     * 
     * @access public
     * @return array $params
     */
    public function getCacheParams($cache_params = array())
    {
        $params = parent::getCacheParams($cache_params);

        // Зависимости от папок: все пункты меню, которые ссылаются на папки.
        $params['folders'] = array();
        $sql = "SELECT folder_id
            FROM menu_items
            WHERE is_active = '1'
            AND folder_id > '0'
            AND group_id = '{$this->menu_group_id}' ";
        $result = $this->container->get('engine.db')->query($sql);
        while ($row = $result->fetchObject()) {
            $params['folders'][$row->folder_id] = 1;
        }

        return $params;
    }

    /**
     *  
     */
    public function getMenuGroupsListArr($selected = 1)
    {
        $data = array();
        $result = $this->DB->query("SELECT * FROM menu_groups ");
        while($row = $result->fetchObject()) {
            $data[$row->group_id] = array(
                'title' => "$row->descr ($row->name)",
                'level' => 0,
                'selected' => $row->group_id == $selected ? 1 : 0,
            );
        }
        return $data;
    }

    /**
     * Получить плоский список пунктов меню для формирования хтмл списков.
     * 
     * Рекурсия.
     */
    protected function _getTreeList($items)
    {
        foreach ($items as $key => $value) {
            $this->_folder_tree_list_arr[$key]['level'] = $this->_tree_level;
            $this->_folder_tree_list_arr[$key]['uri'] = $value['uri'];
            $this->_folder_tree_list_arr[$key]['title'] = $value['title'];
            $this->_folder_tree_list_arr[$key]['descr'] = $value['descr'];
            $this->_folder_tree_list_arr[$key]['options'] = $value['options'];
            $this->_folder_tree_list_arr[$key]['pos'] = $value['pos'];
            $this->_folder_tree_list_arr[$key]['is_active'] = $value['is_active'];
            $this->_folder_tree_list_arr[$key]['selected'] = $value['selected'];
            /*
            if ($cur_folder_id == $value['folder_id']) {
                $this->folder_tree_list_arr[$value['folder_id']]['selected'] = 1;
            } else {
                $this->folder_tree_list_arr[$value['folder_id']]['selected'] = 0;
            }
            */
            if (count($value['items']) > 0) {
                $this->_tree_level++;
                $this->_getTreeList($value['items']);
            }
        }
        $this->_tree_level--;
    }
    
    /**
     * Получить дерево пунктов меню.
     * 
     * @uses Kernel
     * @uses Permissions
     * 
     * @param int $parent_id
     * @param int $max_depth
     * @return array
     */
    public function getTree($parent_id, $max_depth = false)
    { 
        $this->_tree_level++;
        $items = array();

        $is_active = $this->only_is_active ? 'AND i.is_active = 1' : '';
        
        // @todo сделать через класс Folder
        $sql = "SELECT i.item_id, i.is_active, i.pos, i.pid, i.folder_id, i.suffix, i.direct_link, i.title, i.descr, i.options,
                f.permissions, f.title AS folder_title, f.descr AS folder_descr
            FROM menu_items AS i
            LEFT JOIN aaa_engine_folders AS f USING (folder_id)
            WHERE i.group_id = '$this->menu_group_id'
                $is_active
                AND f.is_active = 1
                AND i.pid = $parent_id
            ORDER BY i.pos ";
        $result = $this->container->get('engine.db')->query($sql);
        while($row = $result->fetchObject()) {
            // проверяем возможность на чтение и просмотр папки.
            if ($this->engine('permissions')->isAllowed('folder', 'read', $row->permissions) == 0 
                or $this->engine('permissions')->isAllowed('folder', 'view', $row->permissions) == 0
            ) {
                continue; //echo "$row->folder_title";    
            }
            
            // копаем до указанной глубины.
            if ($max_depth != false and $max_depth < $this->_tree_level) {
                continue;
            }
            
            $uri = empty($row->direct_link) ? $this->engine('folder')->getUri($row->folder_id) : $row->direct_link;

            $title = empty($row->title) ? $row->folder_title : $row->title;
            
            $selected = 0;
            if ($this->selected_inheritance) {
                foreach ($this->engine('breadcrumbs')->get() as $breadcrumb) {

                    if ($breadcrumb['uri'] === $uri and ($uri != $this->get('request')->getBaseUrl() . '/' or $this->engine('env')->current_folder_id == 1)) {
                        $selected = 1;
                        break;
                    }
                }
            } elseif ($this->engine('env')->current_folder_id == $row->folder_id) {
                $selected = 1;
            }
            
            $items[$row->item_id] = array(
                'selected'  => $selected,
                'uri'       => $uri,
                'title'     => $title,
                'descr'     => $row->folder_descr,
                'folder_id' => $row->folder_id,
                'options'   => unserialize($row->options),
                // '_temp_group_id' => $group_id,
                'pid'       => $row->pid,
                'pos'       => $row->pos,
                'is_active' => $row->is_active,
                'items'     => $this->getTree($row->item_id, $max_depth),
                );
            
        } // end while $row
        
        $this->_tree_level--;
        return $items;
    }
}
