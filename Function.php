

// Register Custom Post Type Course
function create_course_cpt() {

	$labels = array(
		'name' => _x( 'Courses', 'Post Type General Name', 'textdomain' ),
		'singular_name' => _x( 'Course', 'Post Type Singular Name', 'textdomain' ),
		'menu_name' => _x( 'Courses', 'Admin Menu text', 'textdomain' ),
		'name_admin_bar' => _x( 'Course', 'Add New on Toolbar', 'textdomain' ),
		'archives' => __( 'Course Archives', 'textdomain' ),
		'attributes' => __( 'Course Attributes', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Course:', 'textdomain' ),
		'all_items' => __( 'All Courses', 'textdomain' ),
		'add_new_item' => __( 'Add New Course', 'textdomain' ),
		'add_new' => __( 'Add New', 'textdomain' ),
		'new_item' => __( 'New Course', 'textdomain' ),
		'edit_item' => __( 'Edit Course', 'textdomain' ),
		'update_item' => __( 'Update Course', 'textdomain' ),
		'view_item' => __( 'View Course', 'textdomain' ),
		'view_items' => __( 'View Courses', 'textdomain' ),
		'search_items' => __( 'Search Course', 'textdomain' ),
		'not_found' => __( 'Not found', 'textdomain' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
		'featured_image' => __( 'Featured Image', 'textdomain' ),
		'set_featured_image' => __( 'Set featured image', 'textdomain' ),
		'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
		'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
		'insert_into_item' => __( 'Insert into Course', 'textdomain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Course', 'textdomain' ),
		'items_list' => __( 'Courses list', 'textdomain' ),
		'items_list_navigation' => __( 'Courses list navigation', 'textdomain' ),
		'filter_items_list' => __( 'Filter Courses list', 'textdomain' ),
	);
	$args = array(
		'label' => __( 'Course', 'textdomain' ),
		'description' => __( '', 'textdomain' ),
		'labels' => $labels,
		'menu_icon' => 'dashicons-admin-collapse',
		'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
		'taxonomies' => array(),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true,
		'has_archive' => true,
		'hierarchical' => true,
		'exclude_from_search' => false,
		'show_in_rest' => true,
		'publicly_queryable' => true,
		'capability_type' => 'page',
	);
	register_post_type( 'course', $args );

}
add_action( 'init', 'create_course_cpt', 0 );



class Course_PDF_Manager {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_pdf_meta_box'));
        add_action('save_post', array($this, 'save_pdf_meta_box'));
        add_action('wp_ajax_upload_course_pdf', array($this, 'handle_pdf_upload'));
        add_action('wp_ajax_delete_course_pdf', array($this, 'handle_pdf_delete'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add meta box to course posts
     */
    public function add_pdf_meta_box() {
        add_meta_box(
            'course_pdfs_meta_box',
            'ملفات الدورة PDF',
            array($this, 'render_pdf_meta_box'),
            'course', // Your post type
            'normal',
            'high'
        );
    }
    
    /**
     * Render the PDF upload meta box
     */
    public function render_pdf_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('course_pdfs_meta_box', 'course_pdfs_meta_box_nonce');
        
        // Get existing PDFs
        $pdfs = get_post_meta($post->ID, '_course_pdfs', true);
        if (!is_array($pdfs)) {
            $pdfs = array();
        }
        
        ?>
        <div id="course-pdf-manager">
            <div class="pdf-upload-area">
                <input type="file" id="pdf-file-input" multiple accept=".pdf" style="display: none;">
                <button type="button" id="upload-pdf-btn" class="button button-primary">
                    📄 Upload PDF Files
                </button>
                <p class="description">Select multiple PDF files (Ctrl+Click or Shift+Click)</p>
            </div>
            
            <div id="pdf-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <span class="progress-text">Uploading...</span>
            </div>
            
            <div id="pdf-list" class="pdf-files-list">
                <?php if (!empty($pdfs)): ?>
                    <?php foreach ($pdfs as $index => $pdf): ?>
                        <div class="pdf-item" data-index="<?php echo $index; ?>">
                            <div class="pdf-info">
                                <span class="pdf-icon">📄</span>
                                <div class="pdf-details">
                                    <input type="text" name="course_pdfs[<?php echo $index; ?>][title]" 
                                           value="<?php echo esc_attr($pdf['title']); ?>" 
                                           placeholder="PDF Title" class="pdf-title-input">
                                    <div class="pdf-meta">
                                        <span class="filename"><?php echo esc_html($pdf['filename']); ?></span>
                                        <span class="filesize">(<?php echo size_format($pdf['filesize']); ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pdf-actions">
                                <a href="<?php echo esc_url($pdf['url']); ?>" target="_blank" class="button button-small">View</a>
                                <button type="button" class="button button-small delete-pdf" data-index="<?php echo $index; ?>">Delete</button>
                                <span class="drag-handle">⋮⋮</span>
                            </div>
                            <input type="hidden" name="course_pdfs[<?php echo $index; ?>][url]" value="<?php echo esc_url($pdf['url']); ?>">
                            <input type="hidden" name="course_pdfs[<?php echo $index; ?>][filename]" value="<?php echo esc_attr($pdf['filename']); ?>">
                            <input type="hidden" name="course_pdfs[<?php echo $index; ?>][filesize]" value="<?php echo esc_attr($pdf['filesize']); ?>">
                            <input type="hidden" name="course_pdfs[<?php echo $index; ?>][attachment_id]" value="<?php echo esc_attr($pdf['attachment_id']); ?>">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="pdf-message" class="notice" style="display: none;"></div>
        </div>
        
        <style>
        #course-pdf-manager {
            padding: 10px 0;
        }
        
        .pdf-upload-area {
            margin-bottom: 20px;
            padding: 20px;
            border: 2px dashed #ddd;
            text-align: center;
            background: #fafafa;
        }
        
        .pdf-files-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .pdf-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
        }
        
        .pdf-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }
        
        .pdf-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .pdf-details {
            flex-grow: 1;
        }
        
        .pdf-title-input {
            width: 100%;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .pdf-meta {
            font-size: 12px;
            color: #666;
        }
        
        .pdf-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .drag-handle {
            cursor: move;
            color: #999;
            font-size: 16px;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 14px;
            color: #666;
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($hook == 'post.php' || $hook == 'post-new.php') {
            if ($post_type == 'course') {
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('course-pdf-manager', get_template_directory_uri() . '/js/course-pdf-manager.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
                wp_localize_script('course-pdf-manager', 'coursePdfAjax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('course_pdf_nonce'),
                    'post_id' => isset($_GET['post']) ? $_GET['post'] : ''
                ));
            }
        }
    }
    
    /**
     * Handle PDF file upload via AJAX
     */
    public function handle_pdf_upload() {
        // Check nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'course_pdf_nonce') || !current_user_can('edit_posts')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $uploaded_files = array();
        
        if (!empty($_FILES['pdf_files'])) {
            $files = $_FILES['pdf_files'];
            $file_count = count($files['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] == 0) {
                    // Prepare file array for wp_handle_upload
                    $file = array(
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    );
                    
                    // Upload file
                    $upload = wp_handle_upload($file, array('test_form' => false));
                    
                    if (!isset($upload['error'])) {
                        // Create attachment
                        $attachment = array(
                            'post_mime_type' => $upload['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );
                        
                        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
                        
                        if (!is_wp_error($attachment_id)) {
                            // Generate attachment metadata
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);
                            
                            $uploaded_files[] = array(
                                'attachment_id' => $attachment_id,
                                'url' => $upload['url'],
                                'filename' => basename($upload['file']),
                                'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                                'filesize' => $file['size']
                            );
                        }
                    }
                }
            }
        }
        
        wp_send_json_success($uploaded_files);
    }
    
    /**
     * Handle PDF deletion via AJAX
     */
    public function handle_pdf_delete() {
        if (!wp_verify_nonce($_POST['nonce'], 'course_pdf_nonce') || !current_user_can('edit_posts')) {
            wp_die('Security check failed');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $deleted = wp_delete_attachment($attachment_id, true);
        
        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete file');
        }
    }
    
    /**
     * Save PDF meta box data
     */
    public function save_pdf_meta_box($post_id) {
        // Check if user has permission to edit the post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['course_pdfs_meta_box_nonce']) || !wp_verify_nonce($_POST['course_pdfs_meta_box_nonce'], 'course_pdfs_meta_box')) {
            return;
        }
        
        // Avoid autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save PDF data
        if (isset($_POST['course_pdfs'])) {
            $pdfs = array();
            foreach ($_POST['course_pdfs'] as $pdf_data) {
                if (!empty($pdf_data['url'])) {
                    $pdfs[] = array(
                        'attachment_id' => intval($pdf_data['attachment_id']),
                        'url' => esc_url_raw($pdf_data['url']),
                        'filename' => sanitize_text_field($pdf_data['filename']),
                        'title' => sanitize_text_field($pdf_data['title']),
                        'filesize' => intval($pdf_data['filesize'])
                    );
                }
            }
            update_post_meta($post_id, '_course_pdfs', $pdfs);
        } else {
            delete_post_meta($post_id, '_course_pdfs');
        }
    }
    
    /**
     * Get course PDFs for frontend display
     */
    public static function get_course_pdfs($post_id) {
        $pdfs = get_post_meta($post_id, '_course_pdfs', true);
        return is_array($pdfs) ? $pdfs : array();
    }
}

// Initialize the class
new Course_PDF_Manager();
