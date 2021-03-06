.. include:: ../Includes.txt



.. _inline-editing:

Inline Editing
------------

After the frontend editing have been activated from within TYPO3´s backend
there are one scenario that needs to been taking into account. It is what
kind of templating engine are used for the frontend template for the websites
that you are using.

.. _css-styled-content:

CSS Styled Content
""""""""""""""""""

If the installation are using the well known (and old) extension which is called
css_styled_content are being used. The functionality comes straight out of the
box and the editing can start directly.

.. _fluid-styled-content:

Fluid Styled Content
""""""""""""""""""""

When it comes to fluid_styled_content there are some things that needs to be
adjusted to your template to get the editing to work. First of all there is
a view helper that needs to be included and configured.

First, find the content that you want editable and wrap it with the view
helper:

.. code-block:: html

	<core:contentEditable table="tt_content" field="bodytext" uid="{item.uid}">
		{item.bodytext}
	</core:contentEditable>

The available options are:

- *table*: The database table name to where the data should be saved
- *field*: The database field to where the data should be saved
- *uid*: The database field to where the data should be saved

A full example looks like this:

.. code-block:: html

	<core:contentEditable table="{item.table}" field="{item.field}" uid="{item.uid}">
		{item.bodytext}
	</core:contentEditable>

The output would then look like the following in frontend edit mode:

.. code-block:: html

	<div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
		This is the content text to edit
	</div>

While not in frontend edit mode the output are the following:

.. code-block:: html

	This is the content text to edit

.. _typoscript:

TypoScript
""""""""""

If you are listing elements with TypoScript only, you can still include the editing icons using the included hook into TYPO3 rendering process.
This example lists editable the frontend user names and emails:

.. code-block:: typoscript

	page.20 = CONTENT
	page.20 {
		table = fe_users
		select.pidInList = 38
		renderObj = COA
		renderObj.10 = TEXT
		renderObj.10 {
			ield = username
			wrap = Username:|<br/>
			stdWrap.editIcons = fe_users: username
			stdWrap.editIcons.beforeLastTag = 1
		}
		renderObj.20 = TEXT
		renderObj.20 {
			field = email
			wrap = Email:|<br/><br/>
			stdWrap.editIcons = fe_users:email
			stdWrap.editIcons.beforeLastTag = 1
		}
		stdWrap.editIcons = pages:users
		stdWrap.editIcons.hasEditableFields = 1
	}
