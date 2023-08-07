<?php
session_start();  
/*
Plugin Name: CashToken Rewards
Version: 1.0 
Plugin URI: https://github.com/tumazfresh/cashtokenplugin
Description: CashToken Rewards is an innovative WordPress plugin designed to showcase a custom permission callback using the bearer token scheme. With this plugin, users can experience the power of token-based authentication, enabling seamless access to restricted resources and functionalities within their WordPress environment.
Version: 1.0
Author: Oboyi Thompson
Author URI: https://eiomra.com
*/
 
register_activation_hook( __FILE__, 'jal_install' ); 

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'tumazkey';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id INT NOT NULL AUTO_INCREMENT,
        tumaztoken VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

}


// Register activation hook
register_activation_hook(__FILE__, 'cashtoken_plugin_activation');

function cashtoken_plugin_activation() {
    // Get the plugin directory path
    $plugin_dir = plugin_dir_path(__FILE__);

    // Create the content for the oauth-callback.php file
    $content =  '<?php' . "\n" .
    'session_start();' . "\n" .
    'if (isset($_GET[\'code\'])) {' . "\n" .
    '    $authorization_code = $_GET[\'code\'];' . "\n" .
    '    if (!isset($_SESSION[\'code_verifier\'])) {' . "\n" .
    '        echo "Error: Code verifier not found.";' . "\n" .
    '        exit;' . "\n" .
    '    }' . "\n" .
    '    $code_verifier = $_SESSION[\'code_verifier\'];' . "\n" .
    '    $token_endpoint = \'https://id-sandbox.cashtoken.africa/oauth/token\';' . "\n" .
    '    $client_id = \'wprQYMZBqqx-dgszFUfQG\';' . "\n" .
    '    $redirect_uri = \'http://localhost:3000/oauth-callback\';' . "\n" .
    '    $data = array(' . "\n" .
    '        \'grant_type\' => \'authorization_code\',' . "\n" .
    '        \'code\' => $authorization_code,' . "\n" .
    '        \'client_id\' => $client_id,' . "\n" .
    '        \'redirect_uri\' => $redirect_uri,' . "\n" .
    '        \'code_verifier\' => $code_verifier' . "\n" .
    '    );' . "\n" .
    '    $ch = curl_init($token_endpoint);' . "\n" .
    '    curl_setopt($ch, CURLOPT_POST, true);' . "\n" .
    '    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));' . "\n" .
    '    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);' . "\n" .
    '    $response = curl_exec($ch);' . "\n" .
    '    if ($response === false) {' . "\n" .
    '        echo "Error exchanging authorization code for access token: " . curl_error($ch);' . "\n" .
    '        exit;' . "\n" .
    '    }' . "\n" .
    '    curl_close($ch);' . "\n" .
    '    $token_data = json_decode($response, true);' . "\n" .
    '    if (isset($token_data[\'access_token\'])) {' . "\n" .
    '        $access_token = $token_data[\'access_token\'];' . "\n" .
    '        $_SESSION[\'access_token\'] = $access_token;' . "\n" .

    'require_once(\'wp-load.php\');' . "\n" .
        ' global $wpdb;' . "\n" .
        '$table_name = $wpdb->prefix . \'tumazkey\';' . "\n" .
        '$query = "SELECT tumaztoken FROM $table_name ORDER BY id DESC LIMIT 1";' . "\n" .
        '$result = $wpdb->get_var($query);' . "\n" .
        '$resultq = "Location:";' . "\n" .
        'header($resultq.$result);' . "\n" .
    '        exit;' . "\n" .
    '    } else {' . "\n" .
    '        echo "Error exchanging authorization code for access token.";' . "\n" .
    '        exit;' . "\n" .
    '    }' . "\n" .
    '} else {' . "\n" .
    '    echo "Error: Authorization code not found.";' . "\n" .
    '}' . "\n" .
    '$response = curl_exec($ch);' . "\n" .
    'if ($response === false) {' . "\n" .
    '    echo "Error executing cURL request: " . curl_error($ch);' . "\n" .
    '    exit;' . "\n" .
    '}' . "\n" .
    '$token_data = json_decode($response, true);' . "\n" .
    'if (json_last_error() !== JSON_ERROR_NONE) {' . "\n" .
    '    echo "Error parsing JSON response: " . json_last_error_msg();' . "\n" .
    '    exit;' . "\n" .
    '}' . "\n" .
    '?>';

    // Generate the file path and name
    $file_path = $plugin_dir . '../../../oauth-callback.php';

    // Create the oauth-callback.php file in the plugin directory
    file_put_contents($file_path, $content);
}


// Enqueue Bootstrap CSS in WordPress head
function enqueue_bootstrap_css() {
    // Define the path to your plugin's CSS directory
    $css_path = plugins_url('css/bootstrap.min.css', __FILE__);

    // Enqueue the Bootstrap CSS file
    wp_enqueue_style('bootstrap-css', $css_path, array(), '4.5.2', 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_css');


// Add the admin menu and button
function cashtoken_plugin_admin_menu() {
    add_menu_page(
        'CashToken Plugin',
        'CashToken',
        'manage_options',
        'cashtoken_plugin',
        'cashtoken_plugin_page',
        'dashicons-image-filter'
    );
}
add_action('admin_menu', 'cashtoken_plugin_admin_menu');

// Callback function for the admin page
function cashtoken_plugin_page() { 
    echo '<h1>CashToken Plugin Settings/Details</h1>';
    echo '<p>Welcome to the settings page for your custom plugin!</p>';  
    global $wpdb;
    $table_name = $wpdb->prefix . 'tumazkey';

    // Check if the form is submitted
    if (isset($_POST['update_token'])) {
        // Get the new token value from the input field
        $new_token = sanitize_text_field($_POST['token']);

        // Check if a record already exists
        $existing_record = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");

        if ($existing_record) {
            // Update the 'tumaztoken' column
            $wpdb->update($table_name, array('tumaztoken' => $new_token), array('id' => $existing_record->id));
            echo '<div class="notice notice-success"><p>Link updated successfully!</p></div>';
        } else {
            // Insert a new entry into the 'tumazkey' table
            $wpdb->insert($table_name, array('tumaztoken' => $new_token));
            echo '<div class="notice notice-success"><p>Link added successfully!</p></div>';
        }
    }

    // Get the existing token value (if any)
    $existing_token = $wpdb->get_var("SELECT tumaztoken FROM $table_name LIMIT 1");
    ?>
    <div class="wrap">
        <h1>Redirect link after user's successful login</h1>
        <form method="post">
            <p>
                <label for="token">Callback URL:</label>
                <input type="text" name="token" id="token" value="<?php echo esc_attr($existing_token); ?>">
            </p>
            <p>
                <input type="submit" name="update_token" class="button button-primary" value="Update Link">
            </p>
        </form>
    </div>
    <?php 
    echo '<p>Use this shortcode to display all posts [cashtoken_posts]  </p>'; 
    ?>
  
    <?php
}

// Custom permission callback function for bearer token scheme
function cashtoken_callback() {
    $token = $_SESSION['access_token'];

    $secret_bearer_token = $_SESSION['access_token'];

    // Verify the token
    if ($token !== $secret_bearer_token) {
        return new WP_Error('rest_forbidden', __('Invalid bearer token.', 'custom-bearer-auth'), array('status' => 403));
    }
    

    // If the token is valid, return true to allow access
    return true;
}

// Custom endpoint callback to fetch posts
function fetch_posts_endpoint_callback($request) {
     // Check if the 'access_token' exists in the session
     if (!isset($_SESSION['access_token'])) {
        // Return 'No access granted' if the access token is not set
        $response_data = array('message' => 'No access granted');
        return rest_ensure_response($response_data);
    }

    // If the token is valid, proceed to fetch all posts
    $posts = fetch_all_posts();

    // Return the response data in the API format.
    return rest_ensure_response($posts);
     
}

// Register the custom endpoint
function register_fetch_posts_endpoint() {
    register_rest_route('cashtoken/v1', '/tokenposts', array(
        'methods' => 'GET',
        'callback' => 'fetch_posts_endpoint_callback',
        'permission_callback' => 'cashtoken_callback', // Use the custom permission callback.
    ));
}

// Hook the function to the rest_api_init action.
add_action('rest_api_init', 'register_fetch_posts_endpoint');

// Function to fetch all posts
function fetch_all_posts() {
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1, // Fetch all posts
    );

    $query = new WP_Query($args);
    $posts = $query->get_posts();

    // Prepare the data to be returned in the API response
    $response_data = array();
    foreach ($posts as $post) {
        $response_data[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content, 
            
            // Include other post data you want to expose in the API response.
        );
    }

    return $response_data;
}

// Shortcode function to display a single fetched post using the custom endpoint
function display_fetched_post_shortcode($atts) {
    if (!isset($_SESSION['access_token'])) {
        // Your authorization code handling code here.
        authorize_user();

        // Show a message to the user that they need to authorize the app
        return 'Please wait while we authorize your access...';
    }

    $userData = fetch_all_posts();
    if (empty($userData)) {
        return 'No posts found.';
    }

    // Display the first post
    echo '<h2>' . esc_html($userData[0]['title']) . '</h2>';
    echo '<div>' . wpautop($userData[0]['content']) . '</div>';
}

// Register the shortcode to display a single post
add_shortcode('display_fetched_post', 'display_fetched_post_shortcode');

// Shortcode function to display all fetched posts using the custom endpoint
function cashtoken_posts_shortcode($atts) {
    if (!isset($_SESSION['access_token'])) {
        // Your authorization code handling code here.
        authorize_user();

        // Show a message to the user that they need to authorize the app
        return 'Please wait while we authorize your access...';
    }

    $userData = fetch_all_posts();
    if (empty($userData)) {
        return 'No posts found.';
    }

    // Display all posts
       // Start the Bootstrap card layout
       $output = '
       <div class="container text-center">
       <div class="row row-cols-1 row-cols-sm-3 row-cols-md-3">';

       // Loop through the posts and generate Bootstrap cards
       foreach ($userData as $post) {
        $featured_image = get_the_post_thumbnail_url($post['id'], 'large');

           $output .= '
  <div class="col"> 
  <div class="card">
  <img src="' . esc_url($featured_image) . '" class="card-img-top" alt="' . esc_attr($post['title']) . '">
<div class="card-body">
<h5 class="card-title">' . esc_html($post['title']) . '</h5>
<p class="card-text"> 
<div class="col">
    <div class="container text-start">
<div class="row row-cols-1 row-cols-sm-1 row-cols-md-1"> 
    <div class="col">' . wp_trim_words($post['content'], 20) . '</div> 
   </div>
</div></div> 
</p>
<p><a href="#" class="btn btn-danger" style="color:#fff;"> Read More </a></p>

</div>
</div>
  </div>

 ';
       }
   
       // End the Bootstrap card layout
       $output .= '</div></div>';
   
       return $output;
   
}
// Function to handle the OAuth 2.0 authorization flow
function authorize_user() {
    session_start();

    $authorization_endpoint = 'https://id-sandbox.cashtoken.africa/oauth/authorize';
    $client_id = 'wprQYMZBqqx-dgszFUfQG';
    $redirect_uri = 'http://localhost:3000/oauth-callback';
    $scope = 'openid email profile';
    $code_verifier = base64UrlEncode(random_bytes(32));

    $code_challenge = base64UrlEncode(hash('sha256', $code_verifier, true));
    $_SESSION['code_verifier'] = $code_verifier;
    $authorization_url = sprintf(
        '%s?response_type=code&client_id=%s&redirect_uri=%s&scope=%s&code_challenge=%s&code_challenge_method=S256',
        $authorization_endpoint,
        urlencode($client_id),
        urlencode($redirect_uri),
        urlencode($scope),
        urlencode($code_challenge)
    );

    header('Location: ' . $authorization_url);
    exit;
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Register the shortcode to display all posts
add_shortcode('cashtoken_posts', 'cashtoken_posts_shortcode');
