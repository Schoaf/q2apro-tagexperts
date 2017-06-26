<?php
/*
	Question2Answer Plugin: q2apro Tag Experts
	Plugin Author URI: http://www.q2apro.com/
	License: http://www.gnu.org/licenses/gpl.html
*/

	class q2apro_tagexperts
	{
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		// for display in admin interface under admin/pages
		function suggest_requests() 
		{	
			return array(
				array(
					'title' => 'Tag Experts', // title of page
					'request' => 'tagexperts', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='tagexperts') 
			{
				return true;
			}

			return false;
		}

		function process_request($request)
		{
		
			// get param from URL
			$tag = qa_get('tag');
			
			// start
			$qa_content = qa_content_prepare();
			$qa_content['custom'] = '';
			
			$qa_content['title'] = 'Tag Experts';
			
			// return if not admin
			$level=qa_get_logged_in_level();
			if ($level<QA_USER_LEVEL_ADMIN) // or QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_EXPERT
			{
				$qa_content['error'] = 'You are not allowed to access this page.';
				return $qa_content;
			}
			
			if(empty($tag))
			{
				$qa_content['error'] = 'No tag specified.';
				$qa_content['custom'] .= '
				<form method="GET" class="newtagform">
					<p>
						Enter a tag: 
					</p>
					<input type="text" name="tag" placeholder="Enter tag here" autofocus>
					<button type="button">Send</button>
				</form>
				';
				return $qa_content;
			}

			$qa_content['title'] = 'Experts for <a target="_blank" href="'.qa_path('tag').'/'.$tag.'">"'.$tag.'"</a>';
			
			// read all questions with specified tag 
			$tagsQuestions = qa_db_read_all_values(
							qa_db_query_sub('SELECT `postid` FROM `^posts`
											 WHERE `type` = "Q"
											 AND FIND_IN_SET("'.$tag.'", `tags`) 
											 AND acount > 0 
											'));
			
			if(empty($tagsQuestions))
			{
				$qa_content['error'] = 'Sorry, no tag found.';
				$qa_content['custom'] .= '
				<form method="GET" class="newtagform">
					<p>
						Enter another tag: 
					</p>
					<input type="text" name="tag" placeholder="Enter tag here" autofocus>
					<button type="button">Send</button>
				</form>
				';
				return $qa_content;
			}

			$quids = join(',',$tagsQuestions);
			
			// memo selchildid is best-answer 
			// get all answers to those tag-questions with data 
			$answers_qu = qa_db_read_all_assoc(
							qa_db_query_sub('SELECT `userid`, `postid`, `upvotes` FROM `^posts`
											 WHERE `type` = "A" 
											 AND `userid` IS NOT NULL 
											 AND `parentid` IN('.$quids.')
											'));
			$upvotecount = [];
			$answercount = [];
			$answerids = [];
			foreach($answers_qu as $dat)
			{
				if(isset($upvotecount[$dat['userid']]))
				{
					$upvotecount[$dat['userid']] += $dat['upvotes'];
					$answercount[$dat['userid']]++;
					$answerids[$dat['userid']] .= ','.$dat['postid'];
				}
				else {
					$upvotecount[$dat['userid']] = $dat['upvotes'];
					$answercount[$dat['userid']] = 1;
					$answerids[$dat['userid']] = $dat['postid'];
				}
			}
			
			// do statistics 
			arsort($upvotecount);
			
			$qa_content['custom'] .= '
			
			<p>
				Click on the table headers to sort.
			</p>
			
			<table class="tagexperts-table">
				<thead>
					<tr>
						<th>Pos</th>
						<th>User</th>
						<th>Upvotes</th>
						<th>Answers</th>
					</tr>
				</thead>
				<tbody>
			';
			
			$pos = 1;
			foreach($upvotecount as $userid=>$vcount)
			{
				$userhandle = qa_userid_to_handle($userid);
				$qa_content['custom'] .= '
				<tr>
					<td>
						'.$pos++.'
					</td>
					<td>
						<a target="_blank" href="'.qa_path('user').'/'.$userhandle.'">'.$userhandle.'</a>
					</td>
					<td>
						<span class="thmbup">👍</span> '.$vcount.'
					</td>
					<td title="Answer IDs: '.$answerids[$userid].'">
						'.$answercount[$userid].'
					</td>
				</tr>
				';
			
			}
			$qa_content['custom'] .= '
				</tbody>
			</table>
			';
			
			$qa_content['custom'] .= '
			<form method="GET" class="newtagform">
				<p>
					Choose another tag: 
				</p>
				<input type="text" name="tag" placeholder="Enter tag here">
				<button type="button">Send</button>
			</form>
			';
			
			// simple jquery table sort, credits to https://stackoverflow.com/a/19947532/1066234
			$qa_content['custom'] .= "
			<script type=\"text/javascript\">
				$(document).ready(function()
				{
					$('.tagexperts-table th').click(function(){
						var table = $(this).parents('table').eq(0)
						var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
						this.asc = !this.asc
						if (!this.asc){rows = rows.reverse()}
						for (var i = 0; i < rows.length; i++){table.append(rows[i])}
					})
					function comparer(index) {
						return function(a, b) {
							var valA = getCellValue(a, index), valB = getCellValue(b, index)
							return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB)
						}
					}
					function getCellValue(row, index){ return $(row).children('td').eq(index).text() }
				});
			</script>
			";
			
			// CSS 
			$qa_content['custom'] .= '
			<style type="text/css">
				.tagexperts-table {
					border-collapse: collapse;
					width: 100%;
					background: #fff;
				}
				.tagexperts-table th {
					background-color: #326295;
					font-weight: normal;
					color: #fff;
					white-space: nowrap;
					cursor:pointer;
				}
				.tagexperts-table th:first-child, 
				.tagexperts-table td:first-child {
					width:10%;
					text-align:center;
				}
				.tagexperts-table td, .tagexperts-table th {
					padding: 1em 1.5em;
					text-align: left;
				}
				.tagexperts-table tbody th {
					background-color: #2ea879;
				}
				.tagexperts-table tbody tr:nth-child(2n-1) {
					background-color: #f5f5f5;
					transition: all .125s ease-in-out;
				}
				.tagexperts-table tbody tr:hover {
					background-color: rgba(50,98,149,.3);
				}
				.thmbup {
					font-size:20px;
				}
				.newtagform {
					margin-top:40px;
				}
			</style>
			';
			
			return $qa_content;
		}
		
	}; // END q2apro_tagexperts
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
