<?php

class EtherpadIntegration {
  // public $ETHERPAD_API_KEY = '5359cfd882946d4d812be5559c40f8ec70604b620f71150b533a62f0fd2a8988';
  // public $ETHERPAD_URL = 'http://ec2-34-239-49-163.compute-1.amazonaws.com/';
  public $ETHERPAD_API_KEY = '6aa157aa6a64e5222b32a945f717a3c102fc7a741503093f41a9077f376093f0';
  public $ETHERPAD_URL = 'https://ipecase.org:8282';

  public function init () {
    add_action('wp_insert_post', array($this,'perform_etherpad_integration'));
    add_action('the_content', array($this, 'filter_etherpad_content'));
    add_shortcode('etherpad', array($this, 'render_etherpad_shortcode'));
  }

  public function generate_etherpad_script ($id) {
    $user_id = get_current_user_id();
    if (isset($_GET['group'])) {
      $etherpad_group_id = $_GET['group'];
    } else {
      $etherpad_group_id = get_user_meta($user_id, 'etherpad_group_id', true);
    }
    $etherpad_author_id = get_user_meta($user_id, 'etherpad_author_id', true);
    $valid_until = time() + (60 * 60 * 3);

    $session_id = $this->create_etherpad_session($etherpad_group_id, $etherpad_author_id, $valid_until);
    $etherpad_id = get_post_meta($id, $etherpad_group_id, true);

    if ($session_id !== null) {
      $script = '
      <div id="etherpad-iframe-container"></div>
      <style>
          .etherpad-iframe {
              height: 600px;
              width: 100%;
            }
      </style>
      <script type="text/javascript">
        document.cookie = "sessionID=%s;path=/;";
        var iframeContainer = document.querySelector("#etherpad-iframe-container");
        var iframe = document.createElement("iframe");
        iframe.classList.add("etherpad-iframe");
        iframe.src = "https://ipecase.org:8282/p/%s";
        iframeContainer.appendChild(iframe);
      </script>
      ';
      $formatted_script = sprintf($script, $session_id, $etherpad_id);
      $content = $formatted_script;
      return $content;
    } else {
      return 'You are not a part of a learn dash group';
    }
  }

  public function render_etherpad_shortcode ($atts) {
      $post_id = $atts['id'];
      $etherpad_script = $this->generate_etherpad_script($post_id);
      return $etherpad_script;
  }


  public function filter_etherpad_content ($content) {
    if (is_singular('etherpad')) {
      $post_id = get_the_ID();
      $etherpad_script = $this->generate_etherpad_script($post_id);
      return $etherpad_script;
    } else {
      return $content;
    }
  }

  public function create_etherpad_session ($group_id, $author_id, $valid_until) {
    $base_url = $this->ETHERPAD_URL . '/api/1/createSession?apikey=%s&groupID=%s&authorID=%s&validUntil=%d';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY, $group_id, $author_id, $valid_until);
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    if ($body['message'] == 'ok'){
      return $body['data']['sessionID'];
    }
    else {
    }
  }

  public function create_group_pad ($group_id, $post_title, $post_content) {
    $base_url = $this->ETHERPAD_URL . '/api/1/createGroupPad?apikey=%s&groupID=%s&padName=%s&text=%s';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY, $group_id, $post_title, $post_content);
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    if ($body['message'] == 'ok'){
      return $body['data']['padID'];
    } else {
      var_dump($body);
    }
  }

  public function update_etherpad_user_meta ($author_id, $etherpad_author_id, $etherpad_group_id) {
    update_user_meta($author_id, 'etherpad_author_id', $etherpad_author_id);
    update_user_meta($author_id, 'etherpad_group_id', $etherpad_group_id);
  }

  public function get_learndash_groups_users () {
    global $wpdb;
    $results = $wpdb->get_results('SELECT user_id, meta_key, meta_value, user_email, display_name FROM wp_usermeta INNER JOIN wp_users ON wp_users.ID = wp_usermeta.user_id WHERE meta_key LIKE "learndash_group_users%"', 'ARRAY_A');
    return $results;
  }

  public function get_logged_in_user_group () {
    global $wpdb;
    $user_id = get_current_user_id();
    $results = $wpdb->get_results($wpdb->prepare('SELECT meta_value FROM wp_usermeta WHERE user_id = %d', $user_id), 'ARRAY_A');
    //Todo:
    // Tom will pass group_id in query string
    // to automatically populate the correct Etherpad when coming from proctor view

    return $results[0];
  }
  /*
  * Add users to Etherpad instance, and then the pad
  * @param
  **/

  public function create_user_if_not_exists ($nickname, $id) {
    $base_url = $this->ETHERPAD_URL . '/api/1/createAuthorIfNotExistsFor?apikey=%s&name=%s&authorMapper=%d';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY, $nickname, $id );
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    return $body['data']['authorID'];
  }

  public function create_group_if_not_exists ($group_id) {
    $base_url = $this->ETHERPAD_URL . '/api/1/createGroupIfNotExistsFor?apikey=%s&groupMapper=%d';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY, $group_id);
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    return $body['data']['groupID'];
  }

  public function create_groups_and_users_in_etherpad() {
    $groups_users = $this->get_learndash_groups_users();
    $groups = [];
    foreach($groups_users as $data) {
      $user_etherpad_id = $this->create_user_if_not_exists($data['display_name'], $data['user_id']);
      $group_etherpad_id = $this->create_group_if_not_exists($data['meta_value']);
      array_push($groups, $group_etherpad_id);
      $this->update_etherpad_user_meta($data['user_id'], $user_etherpad_id, $group_etherpad_id);
    }

    $unique_groups = array_unique($groups);

    return $unique_groups;

  }


  public function perform_etherpad_integration ($post_id) {
    try {
      $post = get_post($post_id);

      if ($post->post_status == 'publish' && $post->post_type == 'etherpad') {
        $post_title = get_the_title($post_id);

        $post_content = $post->post_content;

         $group_etherpad_ids = $this->create_groups_and_users_in_etherpad();
         $etherpads = [];
         foreach($group_etherpad_ids as $group_id) {
           $etherpad_id = $this->create_group_pad($group_id, $post_title, $post_content);
           $post_meta = [
             'group_id' => $group_id,
             'pad_id' => $etherpad_id
           ];
           array_push($etherpads, $post_meta);
         }

         foreach($etherpads as $etherpad) {
           update_post_meta($post_id, $etherpad['group_id'], $etherpad['pad_id']);
         }

      }
    } catch (Exception $exc) {
      echo($exc);
    }
  }


}