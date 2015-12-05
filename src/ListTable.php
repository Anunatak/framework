<?php

namespace Anunatak\Framework;

class ListTable extends \WP_List_Table
{
    /**
     * Order
     * @var string
     */
    private $order;

    /**
     * Order By
     * @var string
     */
    private $orderby;

    /**
     * Search Query
     * @var string
     */
    private $search;

    /**
     * Posts per page
     * @var integer
     */
    protected $posts_per_page = 5;

    /**
     * Holds the model
     *
     * The model is used to get data from the database.
     *
     * @var Anunatak\AnunaFramework\Models\Model
     */
    protected $model;

    /**
     * Holds options for the list
     *
     * Sets the options that are used for the plugin.
     * For example singular and plural table names.
     *
     * @var array
     */
    protected $options;

    /**
     * Table columns
     * @var array
     */
    protected $columns;

    /**
     * Sortable table columns
     * @var array
     */
    protected $sortable_columns;

    /**
     * Holds the slug for the table
     *
     * It is used to create nonces and sometimes to persist data.
     *
     * @var string
     */
    protected $slug;

    /**
     * Set the variables and start everything up
     *
     * @param Anunatak\AnunaFramework\Models\Model $model The model
     * @param array $options The options for the table
     * @param string $slug The slug
     */
    public function __construct()
    {

    }

    public function initiate($model, array $options, array $columns, array $sortable_columns, $slug)
    {
        $this->model            = $model;
        $this->options          = array_merge(array(
            'singular'      => __( 'Page', ANUNAFRAMEWORK_TEXTDOMAIN ),
            'plural'        => __( 'Page', ANUNAFRAMEWORK_TEXTDOMAIN ),
            'ajax'          => false,
            'search_column' => 'name'
        ), $options);;
        $this->columns          = $columns;
        $this->sortable_columns = $sortable_columns;
        $this->slug             = $slug;
        parent::__construct( $this->options );
        $this->set_order();
        $this->set_orderby();
        $this->set_search();
        $this->prepare_items();
    }

    protected function get_model() {
        return $this->model;
    }

    /**
     * Get the current model
     * @param  string  $orderby
     * @param  string  $order
     * @param  integer $per_page
     * @param  integer $page
     * @return array
     */
    public static function get_data($orderby = 'id', $order = 'desc', $per_page = 5, $page = 1)
    {
        $me    = new static;
        $skip  = $page !== 1 ? $per_page * ($page - 1) : 0;
        $data  = array();
        $model = $me->get_model();
        if($this->search) {
            $model->where($this->options['search_column'], 'LIKE', $this->search);
        }
        $data['items'] = $model->take($per_page)->skip($skip)->orderBy($orderby, $order)->get();
        $data['total'] = $model->count();
        return $data;
    }

    /**
     * Delete an item
     * @param  integer $id
     * @return boolean
     */
    public static function delete_item($id) {
        $me = new static;
    	return $this->model->find($id)->delete();
    }

    /**
     * Set the search parameter
     */
    public function set_search()
    {
        $search = '';
        if ( isset( $_GET['s'] ) AND $_GET['s'] )
            $search = $_GET['s'];
        $this->search = esc_sql( $search );
    }

    /**
     * Set the order parameter
     */
    public function set_order()
    {
        $order = 'DESC';
        if ( isset( $_GET['order'] ) AND $_GET['order'] )
            $order = $_GET['order'];
        $this->order = esc_sql( $order );
    }

    /**
     * Set the orderby parameter
     */
    public function set_orderby()
    {
        $orderby = 'id';
        if ( isset( $_GET['orderby'] ) AND $_GET['orderby'] )
            $orderby = $_GET['orderby'];
        $this->orderby = esc_sql( $orderby );
    }

    /**
     * @see WP_List_Table::ajax_user_can()
     */
    public function ajax_user_can()
    {
        return current_user_can( 'edit_posts' );
    }

    /**
     * @see WP_List_Table::no_items()
     */
    public function no_items()
    {
        echo sprintf( __( 'No %s Found', ANUNAFRAMEWORK_TEXTDOMAIN ), strtolower( $this->options['plural'] ) );
    }

    /**
     * @see WP_List_Table::get_views()
     */
    public function get_views()
    {
        return array();
    }

    /**
     * @see WP_List_Table::get_columns()
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * @see WP_List_Table::get_sortable_columns()
     */
    public function get_sortable_columns()
    {
        return $this->sortable_columns;
    }

    /**
     * Prepare data for display
     * @see WP_List_Table::prepare_items()
     */
    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );

        $per_page     = $this->posts_per_page;
        $current_page = $this->get_pagenum();

        $data = $this->get_data($this->orderby, $this->order, $per_page, $current_page);
        $posts = $data['items'];
        $total_items  = $data['total'];

        $this->set_pagination_args( array (
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );

        $last_post = $current_page * $per_page;
        $first_post = $last_post - $per_page + 1;
        $last_post > $total_items AND $last_post = $total_items;

        $this->items = $posts;
    }


    /**
     * Render a column when no column specific method exist.
     *
     * @param Anunatak\AnunaFramework\Models\Model $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        return $item->$column_name;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param Anunatak\AnunaFramework\Models\Model $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->id
        );
    }

    /**
     * Method for the name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name( $item ) {

        $delete_nonce = wp_create_nonce( 'delete_'. $this->slug );

        $title = '<strong>' . $item->name. '</strong>';

        $actions = [
            'delete' => sprintf( '<a href="?page=%s&action=%s&'.$this->slug.'=%s&_wpnonce=%s">'. __( 'Delete' ) .'</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item->id ), $delete_nonce )
        ];

        return $title . $this->row_actions( $actions );
    }

    /**
     * Override of table nav to avoid breaking with bulk actions & according nonce field
     */
    public function display_tablenav( $which ) {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <!--
            <div class="alignleft actions">
                <?php # $this->bulk_actions( $which ); ?>
            </div>
             -->
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * Disables the views for 'side' context as there's not enough free space in the UI
     * Only displays them on screen/browser refresh. Else we'd have to do this via an AJAX DB update.
     *
     * @see WP_List_Table::extra_tablenav()
     */
    public function extra_tablenav( $which )
    {
        global $wp_meta_boxes;
        $views = $this->get_views();
        if ( empty( $views ) )
            return;

        $this->views();
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => __( 'Delete' )
        ];

        return $actions;
    }

    /**
     * Process the bulk action
     * @return void
     */
    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'tpt_delete_'. $this->slug ) ) {
                die( 'Go get a life script kiddies' );
            }
            else {
                self::delete_item( absint( $_GET['item'] ) );

                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql( $_POST['bulk-delete'] );

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_item( $id );
            }

            wp_redirect( esc_url( add_query_arg() ) );
            exit;
        }
    }
}