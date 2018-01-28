<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Twig\Extension;

use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Repository\ProductRepository;
use Eccube\Service\TaxRuleService;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EccubeExtension extends AbstractExtension
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TaxRuleService
     */
    protected $TaxRuleService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;


    public function __construct(\Twig_Environment $twig, TaxRuleService $TaxRuleService, ProductRepository $productRepository)
    {
        $this->twig = $twig;
        $this->TaxRuleService = $TaxRuleService;
        $this->productRepository = $productRepository;
    }


    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        $RoutingExtension = $this->twig->getExtension(EccubeRoutingExtension::class);

        return array(
            new TwigFunction('calc_inc_tax', array($this, 'getCalcIncTax')),
            new TwigFunction('active_menus', array($this, 'getActiveMenus')),
            // new TwigFunction('url', array($RoutingExtension, 'getUrl'), array('is_safe_callback' => array($RoutingExtension, 'isUrlGenerationSafe'))),
            new TwigFunction('is_object', array($this, 'isObject')),
            new TwigFunction('get_product', array($this, 'getProduct')),
            new TwigFunction('php_*', array($this, 'getPhpFunctions'), array('pre_escape' => 'html', 'is_safe' => array('html'))),
            new TwigFunction('has_errors', array($this, 'hasErrors')),
        );
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('no_image_product', array($this, 'getNoImageProduct')),
            new TwigFilter('date_format', array($this, 'getDateFormatFilter')),
            new TwigFilter('price', array($this, 'getPriceFilter')),
            new TwigFilter('ellipsis', array($this, 'getEllipsis')),
            new TwigFilter('time_ago', array($this, 'getTimeAgo')),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'eccube';
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getCalcIncTax($price, $tax_rate, $tax_rule)
    {
        return $price + $this->TaxRuleService->calcTax($price, $tax_rate, $tax_rule);
    }

    /**
     * Name of this extension
     *
     * @param array $menus
     * @return array
     */
    public function getActiveMenus($menus = array())
    {
        $count = count($menus);
        for ($i = $count; $i <= 2; $i++) {
            $menus[] = '';
        }

        return $menus;
    }

    /**
     * bind から URL へ変換します。
     * \Symfony\Bridge\Twig\Extension\RoutingExtension::getUrl の処理を拡張し、
     * RouteNotFoundException 発生時に E_USER_WARNING を発生させ、
     * 文字列 "/404?bind={bind}" を返します。
     *
     * @param string $name
     * @param array $parameters
     * @param boolean $schemeRelative
     * @return string URL
     */
    public function getUrl($name, $parameters = array(), $schemeRelative = false)
    {
        error_log("hoge1");
        /** @var RoutingExtension $RoutingExtension */
        $RoutingExtension = $this->twig->getExtension(EccubeRoutingExtension::class);
        try {
            return $RoutingExtension->getUrl($name, $parameters, $schemeRelative);
        } catch (RouteNotFoundException $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);
        }

        return $RoutingExtension->getUrl('homepage').'404?bind='.$name;
    }

    /**
     * idで指定したProductを取得
     * Productが取得できない場合、または非公開の場合、商品情報は表示させない。
     * デバッグ環境以外ではProductが取得できなくでもエラー画面は表示させず無視される。
     *
     * @param $id
     * @return Product|null|object
     */
    public function getProduct($id)
    {
        /** @var Product $Product */
        $Product = $this->productRepository->find($id);

        if ($Product) {
            if ($Product->getStatus()->getId() == ProductStatus::DISPLAY_SHOW) {
                return $Product;
            }
        }

        return new Product();
    }

    /**
     * return No Image filename
     *
     * @return string
     */
    public function getNoImageProduct($image)
    {
        return empty($image) ? 'no_image_product.jpg' : $image;
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getDateFormatFilter($date, $value = '', $format = 'Y/m/d')
    {
        if (is_null($date)) {
            return $value;
        } else {
            return $date->format($format);
        }
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getPriceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        // $locale = $this->app['config']['locale'];
        // $currency = $this->app['config']['currency'];

        // FIXME
        $locale = 'ja_JP';
        $currency = 'JPY';
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($number, $currency);
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getEllipsis($value, $length = 100, $end = '...')
    {
        return StringUtil::ellipsis($value, $length, $end);
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getTimeAgo($date)
    {
        return StringUtil::timeAgo($date);
    }

    /**
     * Check if the value is object
     *
     * @param object $value
     * @return bool
     */
    public function isObject($value)
    {
        return is_object($value);
    }

    /**
     * FormView にエラーが含まれるかを返す.
     *
     * @return bool
     */
    public function hasErrors()
    {
        $hasErrors = false;

        $views = func_get_args();
        foreach ($views as $view) {
            if (!$view instanceof FormView) {
                throw new \InvalidArgumentException();
            }
            if (count($view->vars['errors'])) {
                $hasErrors = true;
                break;
            };
        }

        return $hasErrors;
    }
}
