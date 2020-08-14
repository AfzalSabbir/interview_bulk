<?php
namespace Bulkly\Http\Controllers;
use Illuminate\Http\Request;
use Bulkly\SocialPostGroups;
use Bulkly\SocialPosts;
use Bulkly\SocialAccounts;
use Bulkly\BufferPosting;
use Bulkly\RssAutoPost;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use DB;

class PostController extends Controller
{

	// start index

	public function myPosts()
	{
		return view('test.index');
	}

	public function getPostData()
	{
		$item = request()->item ? request()->item : 20;

		$posts = DB::table('buffer_postings')
			->leftjoin('users', 'users.id', '=', 'buffer_postings.user_id')
			->leftjoin('social_post_groups', 'social_post_groups.id', '=', 'buffer_postings.group_id')
			->leftjoin('social_posts', 'social_posts.id', '=', 'buffer_postings.post_id')
			->leftjoin('social_accounts', 'social_accounts.id', '=', 'buffer_postings.account_id')
			->select(
				'social_post_groups.name as group_name',
				'social_post_groups.type as group_type',
				'social_accounts.type as account_type',
				'social_accounts.name as account_name',
				'social_accounts.avatar as account_photo',

				'buffer_postings.*'
			);

		if(request()->search_text) $posts = $posts->where('buffer_postings.post_text', 'like', '%'.request()->search_text.'%');
		if(request()->search_time) $posts = $posts->where('buffer_postings.created_at', 'like', request()->search_time.'%');
		if(request()->search_group) $posts = $posts->where('social_post_groups.type', request()->search_group);

		$posts = $posts->paginate($item);

		return $posts;
	
	}

	public function getGroups()
	{
		return SocialPostGroups::orderBy('type', 'asc')->groupBy('type')->get();
	}




	
		// start haveanyschedulepost
	public function haveanyschedulepost($posts)
	{
		foreach ($posts as $post) {
			if ($post->status == '0'){
				return 'have';
			}
		}
		return 'nothave';
	}

	// end haveanyschedulepost
	// start interval
	public function interval($interval, $frequency)
	{

		if ($interval == 'hourly') {
			$hour = 1;
		}
		if ($interval == 'daily') {
			$hour = 24;
		}
		if ($interval == 'weekly') {
			$hour = 7 * 24;
		}
		if ($interval == 'monthly') {
			$hour = 30 * 24;
		}
		$rawinterval = $hour * 60 * 60;
		$intervals = round($rawinterval / $frequency);

		return $intervals;
	}

}