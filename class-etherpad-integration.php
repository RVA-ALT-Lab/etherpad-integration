<?php

class EtherpadIntegration {
  public $ETHERPAD_API_KEY = '5359cfd882946d4d812be5559c40f8ec70604b620f71150b533a62f0fd2a8988';
  public $ETHERPAD_URL = 'http://ec2-34-239-49-163.compute-1.amazonaws.com/';

  public function init () {
    add_action('wp_insert_post', array($this,'perform_etherpad_integration'));
  }

  public function create_group_pad ($group_id, $post_title, $post_content) {
    $base_url = $this->ETHERPAD_URL . '/api/1/createGroupPad?apikey=%s&groupID=%s&padName=%s&text=%s';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY, $group_id, $post_title, $post_content);
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    if ($body['message'] == 'ok'){
      return $body['data']['padID'];
    }
  }

  public function update_etherpad_user_meta ($author_id, $etherpad_author_id, $etherpad_group_id, $etherpad_pad_id) {
    update_user_meta($author_id, 'etherpad_author_id', $etherpad_author_id);
    update_user_meta($author_id, 'etherpad_group_id', $etherpad_group_id);
    update_user_meta($author_id, 'etherpad_pad_id', $etherpad_pad_id);
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
      $user_etherpad_id = $this->create_user_if_not_exists($data['user_id'], $data['display_name']);
      $group_etherpad_id = $this->create_group_if_not_exists($data['meta_value']);
      array_push($groups, $group_etherpad_id);
    }

    $unique_groups = array_unique($groups);

    return $unique_groups;

  }


  public function perform_etherpad_integration ($post_id) {
    try {
      $post = get_post($post_id);

      if ($post->post_status == 'publish' && $post->post_type == 'etherpad') {
        $post_title = get_the_title($post_id);
        $post_content = get_the_content($post_id);

        $group_etherpad_ids = create_groups_and_users_in_etherpad();
        // foreach $group_etherpad_id run create group pad
        // store in array group id, pad id, set as post meta
      }
    } catch (Exception $exc) {
      var_dump($exc);
    }
  }


}