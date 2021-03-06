<?php

namespace Alnv\CatalogManagerAuctionExtensionBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 *
 * @Route("/auction-api", defaults={"_scope" = "frontend", "_token_check" = false})
 */
class AuctionController extends Controller {


    /**
     *
     * @Route("/create", name="create-auction")
     * @Method({"POST"})
     */
    public function create() {

        $this->container->get( 'contao.framework' )->initialize();

        \System::loadLanguageFile('default');

        $strId = \Input::post('id');
        $strTable = \Input::post('table');
        $objDatabase = \Database::getInstance();
        $floValue = (float) \Input::post('auction_value');
        $arrResponse = [ 'state' => false, 'message' => '', 'domId' => \Input::post('dom_id'), 'id' => $strId, 'table' => $strTable ];

        if ( !$objDatabase->tableExists( $strTable ) ) {

            $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['errorMessage'];
            $this->sendResponse( $arrResponse );
        }

        $objEntity = $objDatabase->prepare( 'SELECT * FROM ' . $strTable . ' WHERE id = ?' )->execute( $strId );

        if ( !$objEntity->numRows ) {

            $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['errorMessage'];
            $this->sendResponse( $arrResponse );
        }

        $intWarningOffer = (int)$objEntity->waring_offer ?: 20;
        $intMinOffer = $objEntity->min_offer ?: 0;

        if ( $intMinOffer > $floValue ) {

            $arrResponse['message'] = sprintf( $GLOBALS['TL_LANG']['MSC']['minOfferMessage'], number_format( $intMinOffer, 0, ',', '.' ) );
            $this->sendResponse( $arrResponse );
        }

        if ( !$objDatabase->tableExists( 'cm_offers' ) ) {

            $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['errorMessage'];
            $this->sendResponse( $arrResponse );
        }

        $blnUpdate = false;
        $intUserId = 0;

        if ( FE_USER_LOGGED_IN ) {

            $objUser = \FrontendUser::getInstance();
            $intUserId = $objUser->id;
        }

        $objOfferEntity = $objDatabase->prepare( 'SELECT * FROM cm_offers WHERE member = ? AND offer_to = ? AND offer_object = ? ORDER BY tstamp DESC' )->limit(1)->execute( $intUserId, $strId, $strTable );

        if ( $objOfferEntity->numRows ) {

            $floDiff = ( ( $floValue - (float) $objOfferEntity->offer_value ) / $floValue ) * 100;

            if ( $floDiff >=  $intWarningOffer ) {

                $arrResponse['message'] = sprintf( $GLOBALS['TL_LANG']['MSC']['warningMessage'], $intWarningOffer . '%' );
            }

            if ( (float) $objOfferEntity->offer_value == $floValue ) {

                $blnUpdate = true;
            }
        }

        $arrOffer = [

            'tstamp' => time(),
            'offer_to' => $strId,
            'member' => $intUserId,
            'offer_value' => $floValue,
            'offer_object' => $strTable,
            'alias' => md5( time() . $intUserId ),
            'title' => $objEntity->title ?: ''
        ];

        if ( !$blnUpdate ) {

            $objDatabase->prepare( 'INSERT INTO cm_offers %s' )->set( $arrOffer )->execute();
        }

        else {

            unset( $arrOffer['title'] );
            unset( $arrOffer['alias'] );

            $objDatabase->prepare( 'UPDATE cm_offers %s WHERE id=?' )->set( $arrOffer )->execute( $objOfferEntity->id );
        }

        $arrResponse['state'] = true;

        if ( !$arrResponse['message'] ) {

            $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['successMessage'];
        }

        if ( \Config::get( 'auction_success_notification_id' ) && $objDatabase->tableExists( 'tl_nc_notification' ) ) {

            $arrTokens = $this->generateTokens( $strId, $strTable );
            $objNotification = \NotificationCenter\Model\Notification::findByPk( \Config::get( 'auction_success_notification_id' ) );

            if ( null !== $objNotification ) {

                $objNotification->send( $arrTokens );
            }
        }

        $this->sendResponse( $arrResponse );
    }


    /**
     *
     * @Route("/delete", name="delete-auction")
     * @Method({"POST"})
     */
    public function delete() {

        $this->container->get( 'contao.framework' )->initialize();

        \System::loadLanguageFile('default');

        $intUserId = 0;
        $strId = \Input::post('id');
        $strTable = \Input::post('table');
        $objDatabase = \Database::getInstance();
        $arrResponse = [ 'state' => false, 'message' => '', 'domId' => \Input::post('dom_id'), 'id' => $strId, 'table' => $strTable ];

        if ( FE_USER_LOGGED_IN ) {

            $objUser = \FrontendUser::getInstance();
            $intUserId = $objUser->id;
        }

        if ( !$strTable || !$strId ) {

            $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['errorMessage'];
            $this->sendResponse( $arrResponse );
        }

        $arrResponse['state'] = true;
        $arrResponse['message'] = $GLOBALS['TL_LANG']['MSC']['deleteMessage'] ?: '';

        $objDatabase->prepare( 'DELETE FROM cm_offers WHERE member = ? AND offer_to = ? AND offer_object = ? ' )->execute( $intUserId, $strId, $strTable );
        $this->sendResponse( $arrResponse );
    }


    /**
     *
     * @Route("/all", name="all-auction")
     * @Method({"GET"})
     */
    public function all() {

        $this->container->get( 'contao.framework' )->initialize();

        \System::loadLanguageFile('default');

        $intUserId = 0;
        $strId = \Input::get('id');
        $strTable = \Input::get('table');
        $objDatabase = \Database::getInstance();
        $arrReturn = [ 'data' => [], 'empty' => true, 'message' => '' ];

        if ( FE_USER_LOGGED_IN ) {

            $objUser = \FrontendUser::getInstance();
            $intUserId = $objUser->id;
        }

        $objEntities = $objDatabase->prepare( 'SELECT * FROM cm_offers WHERE member = ? AND offer_to = ? AND offer_object = ? ORDER BY tstamp DESC' )->execute( $intUserId, $strId, $strTable );

        if ( $objEntities->numRows ) {

            $arrReturn['empty'] = false;

            while ( $objEntities->next() ) {

                $arrRow = $objEntities->row();
                $objDate = new \Date( $objEntities->tstamp );

                $arrRow['date'] = $objDate->datim;
                $arrRow['value'] = number_format( (float) $arrRow['offer_value'], 0, ',', '.');
                $arrRow['offer'] = $this->getOfferObject( $objEntities->offer_to, $objEntities->offer_object );

                $arrReturn['data'][] = $arrRow;
            }
        }

        if ( $arrReturn['empty'] ) {

            $arrReturn['message'] = $GLOBALS['TL_LANG']['MSC']['emptyMessage'];
        }

        header( 'Content-Type: application/json' );
        echo json_encode( $arrReturn, 512 );
        exit;
    }


    protected function sendResponse( $arrResponse ) {

        header( 'Content-Type: application/json' );
        echo json_encode( $arrResponse, 512 );
        exit;
    }


    protected function getOfferObject( $strId, $strTable ) {

        $objDatabase = \Database::getInstance();
        $objEntity = $objDatabase->prepare( 'SELECT * FROM '. $strTable .' WHERE id = ? ' )->limit(1)->execute( $strId );

        return $objEntity->row();
    }


    protected function generateTokens( $strId, $strTable ) {

        $arrTokens = [];
        $arrTokens['admin_email'] = \Config::get('adminEmail');
        $arrEntity = $this->getOfferObject( $strId, $strTable );

        foreach ( $arrEntity as $strFieldname => $strValue ) {

            $arrTokens['offer_object_' . $strFieldname ] = $strValue;
        }

        if ( FE_USER_LOGGED_IN ) {

            $objUser = \FrontendUser::getInstance();
            $arrMember = $objUser->row();

            foreach ( $arrMember as $strFieldname => $strValue ) {

                $arrTokens['member_' . $strFieldname ] = $strValue;
            }
        }

        return $arrTokens;
    }
}