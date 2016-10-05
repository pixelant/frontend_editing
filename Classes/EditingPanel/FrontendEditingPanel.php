<?php
namespace TYPO3\CMS\FrontendEditing\EditingPanel;

use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\JsConfirmation;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Utility\Access;
use TYPO3\CMS\Frontend\View\AdminPanelView;

/**
 * View class for the edit panels in frontend editing.
 */
class FrontendEditingPanel
{
    /**
     * The Content Object Renderer
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Property for accessing TypoScriptFrontendController centrally
     *
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * Property for accessing DatabaseConnection centrally
     *
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var FrontendBackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var \TYPO3\CMS\Core\Imaging\IconFactory
     */
    protected $iconFactory;

    /**
     * Constructor for the edit panel
     *
     * @param DatabaseConnection $databaseConnection
     * @param TypoScriptFrontendController $frontendController
     * @param FrontendBackendUserAuthentication $backendUser
     */
    public function __construct(
        DatabaseConnection $databaseConnection = null,
        TypoScriptFrontendController $frontendController = null,
        FrontendBackendUserAuthentication $backendUser = null
    ) {
        $this->databaseConnection = $databaseConnection ?: $GLOBALS['TYPO3_DB'];
        $this->frontendController = $frontendController ?: $GLOBALS['TSFE'];
        $this->backendUser = $backendUser ?: $GLOBALS['BE_USER'];
        $this->cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $this->cObj->start([]);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Adds an edit icon to the content string. The edit icon links to EditDocumentController
     * with proper parameters for editing the table/fields of the context.
     * This implements TYPO3 context sensitive editing facilities.
     * Only backend users will have access (if properly configured as well).
     *
     * @inheritdoc
     * @return string
     */
    public function editIcons(
        $content,
        $params,
        array $conf,
        $currentRecord,
        array $dataArr,
        $addUrlParamStr,
        $table,
        $editUid,
        $fieldList
    ) {
        $conf = (count($conf) === 0) ? [] : $conf;
        $currentRecord = ($currentRecord === '') ? '' : $currentRecord;
        $dataArr = (count($dataArr) === 0) ? [] : $dataArr;
        $addUrlParamStr = ($addUrlParamStr === '') ? '' : $addUrlParamStr;

        if (Access::isEnabled()) {
            // Special content is about to be shown, so the cache must be disabled.
            $this->frontendController->set_no_cache('Display frontend edit icons', true);

            $editIconsToolBar = '';

            $actionsMoveUpIcon = '<span title="'
                . $this->backendUser->extGetLL('p_moveUp') . '">'
                . $this->iconFactory->getIcon('actions-move-up', Icon::SIZE_SMALL)->render()
                . '</span>';

            $actionsMoveDownIcon = '<span title="'
                . $this->backendUser->extGetLL('p_moveDown') . '">'
                . $this->iconFactory->getIcon('actions-move-down', Icon::SIZE_SMALL)->render()
                . '</span>';

            $actionsSaveIcon = '<span title="'
                . $this->backendUser->extGetLL('p_save') . '">'
                . $this->iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL)->render()
                . '</span>';

            $actionsDeleteIcon = '<span title="'
                . $this->backendUser->extGetLL('p_delete') . '">'
                . $this->iconFactory->getIcon('actions-edit-delete', Icon::SIZE_SMALL)->render()
                . '</span>';

            $editIconsToolBar = $actionsMoveUpIcon . $actionsMoveDownIcon . $actionsSaveIcon . $actionsDeleteIcon;

            $wrappedContent = sprintf(
                '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
                $table,
                $fieldList,
                $editUid,
                $content
            );

            return $wrappedContent;
        } else {
            return $content;
        }
    }

    /**
     * Returns TRUE if the input table/row would be hidden in the frontend,
     * according to the current time and simulate user group
     *
     * @param string $table The table name
     * @param array $row The data record
     * @return bool
     */
    protected function isDisabled($table, array $row)
    {
        $status = false;
        if ($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']] ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group'] &&
            $this->frontendController->simUserGroup &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group']]
                == $this->frontendController->simUserGroup ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['starttime'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['starttime']] > $GLOBALS['EXEC_TIME'] ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime']] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime']] < $GLOBALS['EXEC_TIME']
        ) {
            $status = true;
        }

        return $status;
    }
}
