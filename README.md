# CPT Extensions
CPT Extensions object provides additional enhanced options for WordPress CPT (custom post types) by allowing additional 
options in the CPT post registration. The following enhanced options will be enabled when registering custom post types:

## Enhanced Register Post Type Features:

* default_screen_layout - integer, sets the current user's default screen layout
  which indicates the number of columns when visiting the CPT for the first time.
  Valid values are 1 or 2, with the current default set to 2 columns.
* simplify_publish_box - boolean, simplifies the publish metabox by only
  displaying "Move to Trash", and the "Publish" button.
* update_bulk_messages - boolean, customize the bulk message that appear when
  editing or updating CPTs. I.e. "Post published" can become "Site published",
  etc.
* dragndrop_sortable - boolean, enables drag and drop re-ordering in list view.
  Default is false for disabling drag and drop support.
* metabox_layout - number, indicates enhanced layout to display 0 - normal, 1 -
  tabbed interface, 2 - wizard like interface.
* show_edit_slug_box - boolean, determines if the slugbox is displayed.
* disable_autosave - boolean, determins if WordPress will autosave. Default is
  false.
* remove_bulk_actions - array, bulk item operations to remove from the list view
  drop down combo box, i.e. ['edit', 'trash']. The default is an empty array.
* remove_row_actions - array, row operations to remove from the list view i.e.
  ['view', 'edit']. The default is an empty array.
* remove_quick_edit - boolean, removes the Quick Edit menu option in list view.
  The default is false to allow the Quick Edit menu to appear.
* hide_title_field - boolean, hides the title field from the CPT add and edit
  screen. The default is true to show the title field.
* menu_icon_font - string, supports the display of an icon font for a menu item.
  If the given string is presented with a font name and character value, a menu
  icon css definition is included in the header. Note: the original menu_icon
  value should be set to an empty string.
 
## Enhanced Labels for Custom Post Type Options:
 
* enter_title_here_text - string, the suggestion text that appears in the title
  area of the CPT "Add New" screen. The default is a localized string for the
  value 'Enter title here'.
 
## License & Copyright

CPT-Extensions is Copyright Stephen J Carnam 2012, and is offered under the terms of the GNU General Public License, version 2.