<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\ImportExport\Export\Type;

use PDO;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\Propel;
use Thelia\ImportExport\Export\JsonFileAbstractExport;
use Thelia\Model\Product;

/**
 * Class ProductSEOExport
 * @author Benjamin Perche <bperche@openstudio.fr>
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 * @author Florian Bernard <fbernard@openstudio.fr>
 */
class ProductSEOExport extends JsonFileAbstractExport
{
    const FILE_NAME = 'product_seo';

    protected $orderAndAliases = [
        'product_id' => 'id',
        'product_ref' => 'ref',
        'product_i18n_title' => 'product_title',
        'product_visible' => 'visible',
        'rewriting_url_url'  => 'url',
        'product_i18n_meta_title' => 'page_title',
        'product_i18n_meta_description' => 'meta_description',
        'product_i18n_meta_keywords' => 'meta_keywords',
    ];

    public function getData()
    {
        $locale = $this->language->getLocale();

        $con = Propel::getConnection();
        $query = 'SELECT 
                        product.id as "product_id",
                        product.ref as "product_ref",
                        product_i18n.title as "product_i18n_title",
                        product.visible as "product_visible",
                        rewriting_url.url as "rewriting_url_url",
                        product_i18n.meta_title as "product_i18n_meta_title",
                        product_i18n.meta_description as "product_i18n_meta_description",
                        product_i18n.meta_keywords as "product_i18n_meta_keywords"
                    FROM product
                    LEFT JOIN product_i18n ON product_i18n.id = product.id AND product_i18n.locale = :locale
                    LEFT JOIN rewriting_url ON rewriting_url.view = "'.(new Product())->getRewrittenUrlViewName().'" AND rewriting_url.view_id = product.id
                    ORDER BY product.id'
        ;
        $stmt = $con->prepare($query);
        $stmt->bindValue('locale', $locale);
        $stmt->execute();

        $filename = THELIA_CACHE_DIR . '/export/' . 'product_seo.json';

        if (file_exists($filename)) {
            unlink($filename);
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            file_put_contents($filename, json_encode($row) . "\r\n", FILE_APPEND);
        }

        return $filename;
    }
}
