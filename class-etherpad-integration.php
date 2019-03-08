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

  public function create_group_if_not_exists () {
    $base_url = $this->ETHERPAD_URL . '/api/1/createGroupIfNotExistsFor?apikey=%s&groupMapper=1';
    $formatted_url = sprintf($base_url, $this->ETHERPAD_API_KEY);
    $response = wp_remote_get($formatted_url);
    $body = json_decode($response['body'], true);
    return $body['data']['groupID'];
  }


  public function perform_etherpad_integration ($post_id) {
    $post = get_post($post_id);

    if ($post->post_status == 'publish') {
      $author_id = $post->post_author;
      $author_name = get_user_meta($author_id, 'nickname', true);
      $post_title = get_the_title($post_id);
      $post_content = get_the_content($post_id);
      $group_id = $this->create_group_if_not_exists();
      $author_pad_id = $this->create_user_if_not_exists($author_name, $author_id);
      $pad_id = $this->create_group_pad($group_id, $post_title, $post_content);
      $this->update_etherpad_user_meta($author_id, $author_pad_id, $group_id, $pad_id);
    }
  }


}