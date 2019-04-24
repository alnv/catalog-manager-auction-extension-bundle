<?php

namespace Alnv\CatalogManagerAuctionExtensionBundle\Views;


class FormView {


    protected $strTable = null;
    protected $arrOptions = [];
    protected $strId = null;


    public function __construct( $strTablename, $strId, $arrOptions = [] ) {

        $this->strTable = $strTablename;
        $this->arrOptions = $arrOptions;
        $this->strId = $strId;
    }

    public function parse() {

        $intUserId = 0;
        $objDatabase = \Database::getInstance();
        $objTemplate = new \FrontendTemplate( 'auction_view_form' );

        $objTemplate->hasOffers = false;
        $objTemplate->id = $this->strId;
        $objTemplate->table = $this->strTable;

        $objTemplate->sLabel = $GLOBALS['TL_LANG']['MSC']['slabel'];
        $objTemplate->dLabel = $GLOBALS['TL_LANG']['MSC']['dlabel'];
        $objTemplate->pLabel = $GLOBALS['TL_LANG']['MSC']['plabel'];

        if ( FE_USER_LOGGED_IN ) {

            $objUser = \FrontendUser::getInstance();
            $intUserId = $objUser->id;
        }

        $objEntity = $objDatabase->prepare('SELECT * FROM cm_offers WHERE member = ? AND offer_to = ? AND offer_object = ? ORDER BY tstamp DESC')->limit(1)->execute( $intUserId, $this->strId, $this->strTable );

        if ( $objEntity->numRows && ( !isset( $this->arrOptions['unsetDeleteButton'] ) || $this->arrOptions['unsetDeleteButton'] != '1' ) ) {

            $objTemplate->hasOffers = true;
        }

        return $objTemplate->parse();
    }
}