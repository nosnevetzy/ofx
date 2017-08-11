<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class Ps_FeaturedProducts extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'ps_featuredproducts';
        $this->author = 'PrestaShop';
        $this->version = '2.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Featured products', array(), 'Modules.Featuredproducts.Admin');
        $this->description = $this->trans('Displays featured products in the central column of your homepage.', array(), 'Modules.Featuredproducts.Admin');

        $this->templateFile = 'module:ps_featuredproducts/views/templates/hook/ps_featuredproducts.tpl';
    }

    public function install()
    {
        $this->_clearCache('*');

        Configuration::updateValue('HOME_FEATURED_NBR', 8);
        Configuration::updateValue('HOME_FEATURED_CAT', (int) Context::getContext()->shop->getCategory());
        Configuration::updateValue('HOME_FEATURED_RANDOMIZE', false);

        return parent::install()
            && $this->registerHook('addproduct')
            && $this->registerHook('updateproduct')
            && $this->registerHook('deleteproduct')
            && $this->registerHook('categoryUpdate')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayOrderConfirmation2')
            && $this->registerHook('displayCrossSellingShoppingCart')
            && $this->registerHook('actionAdminGroupsControllerSaveAfter')
        ;
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        return parent::uninstall();
    }

    public function hookAddProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookUpdateProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookDeleteProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookCategoryUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionAdminGroupsControllerSaveAfter($params)
    {
        $this->_clearCache('*');
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    public function getContent()
    {
        $output = '';
        $errors = array();

        if (Tools::isSubmit('submitHomeFeatured')) {
            $nbr = Tools::getValue('HOME_FEATURED_NBR');
            if (!Validate::isInt($nbr) || $nbr <= 0) {
                $errors[] = $this->trans('The number of products is invalid. Please enter a positive number.', array(), 'Modules.Featuredproducts.Admin');
            }

            $cat = Tools::getValue('HOME_FEATURED_CAT');
            if (!Validate::isInt($cat) || $cat <= 0) {
                $errors[] = $this->trans('The category ID is invalid. Please choose an existing category ID.', array(), 'Modules.Featuredproducts.Admin');
            }

            $rand = Tools::getValue('HOME_FEATURED_RANDOMIZE');
            if (!Validate::isBool($rand)) {
                $errors[] = $this->trans('Invalid value for the "randomize" flag.', array(), 'Modules.Featuredproducts.Admin');
            }
            if (isset($errors) && count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('HOME_FEATURED_NBR', (int) $nbr);
                Configuration::updateValue('HOME_FEATURED_CAT', (int) $cat);
                Configuration::updateValue('HOME_FEATURED_RANDOMIZE', (bool) $rand);

                $this->_clearCache('*');

                $output = $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
            }
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ),

                'description' => $this->trans('To add products to your homepage, simply add them to the corresponding product category (default: "Home").', array(), 'Modules.Featuredproducts.Admin'),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Number of products to be displayed', array(), 'Modules.Featuredproducts.Admin'),
                        'name' => 'HOME_FEATURED_NBR',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Set the number of products that you would like to display on homepage (default: 8).', array(), 'Modules.Featuredproducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Category from which to pick products to be displayed', array(), 'Modules.Featuredproducts.Admin'),
                        'name' => 'HOME_FEATURED_CAT',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Choose the category ID of the products that you would like to display on homepage (default: 2 for "Home").', array(), 'Modules.Featuredproducts.Admin'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Randomly display featured products', array(), 'Modules.Featuredproducts.Admin'),
                        'name' => 'HOME_FEATURED_RANDOMIZE',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Enable if you wish the products to be displayed randomly (default: no).', array(), 'Modules.Featuredproducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                ),
            ),
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHomeFeatured';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'HOME_FEATURED_NBR' => Tools::getValue('HOME_FEATURED_NBR', (int) Configuration::get('HOME_FEATURED_NBR')),
            'HOME_FEATURED_CAT' => Tools::getValue('HOME_FEATURED_CAT', (int) Configuration::get('HOME_FEATURED_CAT')),
            'HOME_FEATURED_RANDOMIZE' => Tools::getValue('HOME_FEATURED_RANDOMIZE', (bool) Configuration::get('HOME_FEATURED_RANDOMIZE')),
        );
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('ps_featuredproducts'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('ps_featuredproducts'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $products = $this->getProducts();
        foreach ($products as $p_key => $product) {
           $groups = array();
           $attributes_groups = $this->getAttributesGroups($this->context->language->id, $product['id_product']);
           $groups = $this->setAttributeGroups($attributes_groups, $product['id_product']);
           $products[$p_key]['groups'] = $groups;
        } 

        if (!empty($products)) {
            return array(
                'products' => $products,
                'allProductsLink' => Context::getContext()->link->getCategoryLink($this->getConfigFieldsValues()['HOME_FEATURED_CAT']),
            );
        }
        return false;
    }


     /**
     * Get all available attribute groups
     *
     * @param int $id_lang Language id
     * @param int $id_product Product ID
     * @return array Attribute groups
     */
    public function getAttributesGroups($id_lang, $id_product)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        $sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name,
                    a.`id_attribute`, al.`name` AS attribute_name, a.`color` AS attribute_color, product_attribute_shop.`id_product_attribute`,
                    IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`price`, product_attribute_shop.`ecotax`, product_attribute_shop.`weight`,
                    product_attribute_shop.`default_on`, pa.`reference`, product_attribute_shop.`unit_price_impact`,
                    product_attribute_shop.`minimal_quantity`, product_attribute_shop.`available_date`, ag.`group_type`
                FROM `'._DB_PREFIX_.'product_attribute` pa
                '.Shop::addSqlAssociation('product_attribute', 'pa').'
                '.Product::sqlStock('pa', 'pa').'
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
                LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
                LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`)
                LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group`)
                '.Shop::addSqlAssociation('attribute', 'a').'
                WHERE pa.`id_product` = '.(int)$id_product.'
                    AND al.`id_lang` = '.(int)$id_lang.'
                    AND agl.`id_lang` = '.(int)$id_lang.'
                GROUP BY id_attribute_group, id_product_attribute
                ORDER BY ag.`position` ASC, a.`position` ASC, agl.`name` ASC';
        return Db::getInstance()->executeS($sql);
    }


    /**
     * Yztevenson Bier
     * Get all available attribute groups
     *
     * @param array $attribute_groups comes from getAttributeGroups function
     * @param int $id_product
     * @return array Attribute groups
     */
    public function setAttributeGroups($attributes_groups, $id_product){
        $colors = array();
        if (is_array($attributes_groups) && $attributes_groups) {

                $combination_images = $this->getCombinationImages($this->context->language->id, $id_product);
                $combination_prices_set = array();

                foreach ($attributes_groups as $k => $row) {
                     // Color management
                    if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
                        $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                        $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                        if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                            $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                        }
                        $colors[$row['id_attribute']]['attributes_quantity'] += (int) $row['quantity'];
                    }

                    if (!isset($groups[$row['id_attribute_group']])) {
                        $groups[$row['id_attribute_group']] = array(
                            'group_name' => $row['group_name'],
                            'name' => $row['public_group_name'],
                            'group_type' => $row['group_type'],
                            'default' => -1,
                        );
                    }

                    //set attribute group details
                    $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = array(
                        'name' => $row['attribute_name'],
                        'html_color_code' => $row['attribute_color'],
                        'texture' => (@filemtime(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg')) ? _THEME_COL_DIR_.$row['id_attribute'].'.jpg' : '',
                        'selected' => (isset($product_for_template['attributes'][$row['id_attribute_group']]['id_attribute']) && $product_for_template['attributes'][$row['id_attribute_group']]['id_attribute'] == $row['id_attribute']) ? true : false,
                    );

                    //$product.attributes.$id_attribute_group.id_attribute eq $id_attribute
                    if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                        $groups[$row['id_attribute_group']]['default'] = (int) $row['id_attribute'];
                    }
                    if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                        $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                    }
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int) $row['quantity'];
                }//end of foreach attribute_groups

                // wash attributes list depending on available attributes depending on selected preceding attributes
                $current_selected_attributes = array();
                $count = 0;

                //foreach groups array
                foreach ($groups as &$group) {
                    $count++;
                    if ($count > 1) {
                        //find attributes of current group, having a possible combination with current selected
                        $id_attributes = Db::getInstance()->executeS('SELECT `id_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` pac2 
                            WHERE `id_product_attribute` IN (
                                SELECT pac.`id_product_attribute`
                                    FROM `'._DB_PREFIX_.'product_attribute_combination` pac
                                    INNER JOIN `'._DB_PREFIX_.'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
                                    WHERE id_product = '.$id_product.' AND id_attribute IN ('.implode(',', array_map('intval', $current_selected_attributes)).')
                                    GROUP BY id_product_attribute
                                    HAVING COUNT(id_product) = '.count($current_selected_attributes).'
                            ) AND id_attribute NOT IN ('.implode(',', array_map('intval', $current_selected_attributes)).')');
                        foreach ($id_attributes as $k => $row) {
                            $id_attributes[$k] = (int)$row['id_attribute'];
                        }
                        foreach ($group['attributes'] as $key => $attribute) {
                            if (!in_array((int)$key, $id_attributes)) {
                                unset($group['attributes'][$key]);
                                unset($group['attributes_quantity'][$key]);
                            }
                        }
                    }
                    //find selected attribute or first of group
                    $index = 0;
                    $current_selected_attribute = 0;
                    foreach ($group['attributes'] as $key => $attribute) {
                        if ($index === 0) {
                            $current_selected_attribute = $key;
                        }
                        if ($attribute['selected']) {
                            $current_selected_attribute = $key;
                            break;
                        }
                    }
                    if ($current_selected_attribute > 0) {
                        $current_selected_attributes[] = $current_selected_attribute;
                    }
                }

                 // wash attributes list (if some attributes are unavailables and if allowed to wash it)
                if (!Product::isAvailableWhenOutOfStock($id_product) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                    foreach ($groups as &$group) {
                        foreach ($group['attributes_quantity'] as $key => &$quantity) {
                            if ($quantity <= 0) {
                                unset($group['attributes'][$key]);
                            }
                        }
                    }

                    foreach ($colors as $key => $color) {
                        if ($color['attributes_quantity'] <= 0) {
                            unset($colors[$key]);
                        }
                    }
                }
            }

            return $groups;
    }


    /**
     * Yztevenson Bier
     * Get all available attribute groups
     *
     * @param array $id_lang Language ID
     * @param int $id_product Product ID
     * @return array images 
     */
    public function getCombinationImages($id_lang, $id_product)
    {
        if (!Combination::isFeatureActive()) {
            return false;
        }

        $product_attributes = Db::getInstance()->executeS(
            'SELECT `id_product_attribute`
            FROM `'._DB_PREFIX_.'product_attribute`
            WHERE `id_product` = '.(int)$id_product
        );

        if (!$product_attributes) {
            return false;
        }

        $ids = array();

        foreach ($product_attributes as $product_attribute) {
            $ids[] = (int)$product_attribute['id_product_attribute'];
        }

        $result = Db::getInstance()->executeS('
            SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
            FROM `'._DB_PREFIX_.'product_attribute_image` pai
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = pai.`id_image`)
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_image` = pai.`id_image`)
            WHERE pai.`id_product_attribute` IN ('.implode(', ', $ids).') AND il.`id_lang` = '.(int)$id_lang.' ORDER by i.`position`'
        );

        if (!$result) {
            return false;
        }

        $images = array();

        foreach ($result as $row) {
            $images[$row['id_product_attribute']][] = $row;
        }

        return $images;
    }


    protected function getProducts()
    {
        $category = new Category((int) Configuration::get('HOME_FEATURED_CAT'));

        $searchProvider = new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            $category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();

        $nProducts = Configuration::get('HOME_FEATURED_NBR');
        if ($nProducts < 0) {
            $nProducts = 12;
        }

        $query
            ->setResultsPerPage($nProducts)
            ->setPage(1)
        ;

        if (Configuration::get('HOME_FEATURED_RANDOMIZE')) {
            $query->setSortOrder(SortOrder::random());
        } else {
            $query->setSortOrder(new SortOrder('product', 'position', 'asc'));
        }

        $result = $searchProvider->runQuery(
            $context,
            $query
        );

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        foreach ($result->getProducts() as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }
}
