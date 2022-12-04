<!-- Handles all community pages -->
<?php 
	session_start();
	require 'scripts/db_handler.php';
	readfile("scripts/header.php");
    $cmtyName = strtolower($_GET['cmty']);

    if (empty($cmtyName)) {
        header("Location: index.php");
        exit();
    }

    $sql = "SELECT * FROM communities WHERE cmty_name=?;";
    $statement = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($statement, $sql)) {
        header("Location: index.php");
        exit();
    }

    mysqli_stmt_bind_param($statement, "s", $cmtyName);
    mysqli_stmt_execute(($statement));
    $results = mysqli_stmt_get_result($statement);

    if ($row = mysqli_fetch_assoc($results)) {        
        $descr = $row['about'];
        $total_member_count = $row['member_count'];
        $total_post_count = $row['post_count'];
    }
    else {
        header("Location: ./index.php?error=noCmtyFound");
        exit();
    }

	// Check if the user is logged in or not
	if (!isset($_SESSION['userID'])) { 
		readfile("scripts/logged-out.php");
	} 
	else {
		readfile("scripts/logged-in.php");
	} 
?>

		<!-- Community PAGE:  -->
		<!-- Displays all the posts --> 
            <!-- Main Body -->
			<div class="container-fluid main-body">
				<!-- Body -->
				<div class="container body-wrapper">
					<!-- About the Cmty -->
					<div class="card text-center" id="cmty-card">
						<div class="card-header">
							<!-- Dropdown Cmty -->
							<div class="dropdown">
								<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false" style="background-color:#00274C; color:#FFCB05">
									<label style="font-size: 25px;"><?php echo $cmtyName ?></label> 
								</button>
								<ul class="dropdown-menu dropdown-menu" aria-labelledby="dropdownMenuButton2">
									<li>
										<a class="dropdown-item searching">								
											<form class="d-flex" role="search" method="GET" action="cmty.php">
												<button class="btn material-symbols-outlined" onclick="location.href='index.php'" id="takeMeHome">home</button>
												<input class="form-control me-2 search-bar" type="search" placeholder="community search" aria-label="Search" name="cmty" autocomplete="off">
											</form>
										</a>
									</li>
									<li><hr class="dropdown-divider"></li>
									<?php 
										// print out all communities
										$sql = 'SELECT cmty_name FROM communities;';
										$results = mysqli_query($connection, $sql);

										if (empty($results)) {
											echo '<li style="text-align:center;"><span>No Communities Found :(</span></li>';
										}
										else {
											while ($row = mysqli_fetch_array($results)) { 
												echo '<li><a class="dropdown-item" href="cmty.php?cmty='.$row['cmty_name'].'">'.$row['cmty_name'].'</a></li>';
											}
										}
									?>
								</ul>
							</div>
							<!-- Search bar -->
							<div class="container" id="searchbar">
								<button tabindex="-1" type="submit" class="btn material-symbols-outlined create-btn" title="Browse">search</button>
							</div>
						</div>
						<div class="card-body" style="display: flex; align-items: center; flex-direction: column;">
                            <h5 class="card-title">Welcome to <?php echo $cmtyName ?></h5>
							<p class="card-text" style="width: 60%;">
                                <?php echo $descr ?>
							</p>
							<a href="#" class="btn" id="join-btn">Join</a>
						</div>
						<div class="card-footer text-muted">
							<?php
								if ($total_member_count == 1 && $total_post_count == 1) {
									echo $total_member_count.' Member • '.$total_post_count.' Post';
								}
								else if ($total_member_count == 1) {
									echo $total_member_count.' Member • '.$total_post_count.' Posts';
								}
								else if($total_post_count == 1) {
									echo $total_member_count.' Members • '.$total_post_count.' Post';
								}
								else {
									echo $total_member_count.' Members • '.$total_post_count.' Posts';
								}
							?>
						</div>
					</div>
				
				<!-- No Posts in Cmty -->
					<?php
						$query = "SELECT * FROM posts WHERE community_name=?;";
						if (!mysqli_stmt_prepare($statement, $query)) {
							header("Location: index.php?error=sqlError");
							exit();
						}
						mysqli_stmt_bind_param($statement, "s", $cmtyName);
						mysqli_stmt_execute(($statement));
						$result = mysqli_stmt_get_result($statement);

						$total_rows = mysqli_num_rows($result);
						if ($total_rows == 0) {
					?>
					<!-- Posts Section -->
					<div class="container-fluid posts-wrapper">
						<!-- Posts Content -->
						<div class="container-fluid posts-body">
							<div class="container posts-title">
								<label>Posts</label>
							</div>
							<!-- Bulletin Board -->
							<div class="container stickies-wrapper">
								<ul class="sticky-notes" style="display: flex; flex-direction: column;">
									<h5 style="text-align: center; margin-top: 10px;">Be the first to post!</h5>
									<br>
									<img src="assets/imgs/astronaut.png" alt="mdo" width="200" height="200">
								</ul>
							</div>	
						</div>
					</div>
					<?php
						}
						else {
					?>

					<!-- Top Posts Section -->
					<div class="container-fluid posts-wrapper" id="top-posts">
						<!-- Top Posts Content -->
						<div class="container-fluid posts-body">
							<div class="container posts-title">
								<label>Top Posts</label>
							</div>
							<!-- Bulletin Board -->
							<div class="container stickies-wrapper">
								<!-- Get all posts from DB -->
								<ul class="sticky-notes">
									<?php
                                        $query = "SELECT * FROM posts WHERE community_name=?;";

                                        if (!mysqli_stmt_prepare($statement, $query)) {
                                            header("Location: index.php?error=sqlError");
                                            exit();
                                        }

                                        mysqli_stmt_bind_param($statement, "s", $cmtyName);
                                        mysqli_stmt_execute(($statement));
                                        $result = mysqli_stmt_get_result($statement);

										while($row = mysqli_fetch_array($result)) {
											$title = $row['title'];
											$type = -1;
											$cmtyID = $row['community_id'];
											$description = $row['descr'];
											$cmtyName = $row['community_name'];
											$username = $row['author'];
											$timeDiff = date('m/d/Y h:i:s a', time()) - date("H:i:s",strtotime($row['created_at']));
											$comments = $row['comments'];
											$postid = $row['id'];
						
											if (isset($_SESSION['userID'])) {
												$userid = $_SESSION['userID'];
												$status_query = "SELECT count(*) as cntStatus,type FROM like_unlike WHERE userid=".$userid." and postid=".$postid;
												$status_result = mysqli_query($connection,$status_query);
												$status_row = mysqli_fetch_array($status_result);
												$count_status = $status_row['cntStatus'];

												if($count_status > 0){
													$type = $status_row['type'];
												}
							
												$like_query = "SELECT COUNT(*) AS cntLikes FROM like_unlike WHERE type=1 and postid=".$postid;
												$like_result = mysqli_query($connection,$like_query);
												$like_row = mysqli_fetch_array($like_result);
												$total_likes = $like_row['cntLikes'];
							
												$unlike_query = "SELECT COUNT(*) AS cntUnlikes FROM like_unlike WHERE type=0 and postid=".$postid;
												$unlike_result = mysqli_query($connection,$unlike_query);
												$unlike_row = mysqli_fetch_array($unlike_result);
												$total_unlikes = $unlike_row['cntUnlikes'];
											}
						
									?>
										<li>
											<a class="sticky-note">
												<div class="sticky-note-info">
													<small><?php echo $cmtyName?></small>
													<btn class="bi bi-arrows-angle-expand" data-bs-toggle="modal" data-bs-target="#noteModal"></btn>
												</div>
												<div class="sticky-note-title">
													<h6><?php echo $title?></h6>
												</div>
												<div class="sticky-note-info">
													<small><?php echo $username?> • <?php if ($timeDiff < 1) { echo 'just now';} else {echo $timeDiff.' hour(s) ago';} ?></small> 
												</div>
													<?php
														if (!isset($_SESSION['userID'])) {
															// get likes and dislikes for each post
															$sql = 'SELECT * FROM posts where community_name=?;';
															$statement = mysqli_stmt_init($connection);

															if (!mysqli_stmt_prepare($statement, $sql)) {
																header("Location: index.php?error=sqlError");
																exit();
															}
															else {
																mysqli_stmt_bind_param($statement, "s", $cmtyName);
																mysqli_stmt_execute(($statement));
											
																$results = mysqli_stmt_get_result($statement);
											
																if ($row = mysqli_fetch_assoc($results)) {
																	$likes = $row['likes'];
																	$dislikes = $row['dislikes'];
																	$comments = $row['comments'];
																}
																else {
																	header("Location: index.php?error=sqlError");
																	exit();
																}
															}
													?>
													<div class="interactions">
														<button tabindex="-1" class="bi bi-hand-thumbs-up interaction-btn" onclick="loginAlert()">
															<span class="like-count"><?php echo $likes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-hand-thumbs-down interaction-btn" onclick="loginAlert()">
																<span class="dislike-count"><?php echo $dislikes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-chat-left-text interaction-btn" onclick="loginAlert()">
															<span class="comment-count"><?php echo $comments?></span>
														</button>
													</div>
													<?php
														}
														else {
													?>
													<div class="interactions">
														<button tabindex="-1" class="bi bi-hand-thumbs-up interaction-btn like" id="like_<?php echo $postid; ?>">
															<span class="like-count" id="likes_<?php echo $postid; ?>"><?php echo $total_likes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-hand-thumbs-down interaction-btn unlike" id="unlike_<?php echo $postid; ?>">
																<span class="dislike-count" id="unlikes_<?php echo $postid; ?>"><?php echo $total_unlikes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-chat-left-text interaction-btn">
															<span class="comment-count"><?php echo ' '.$comments?></span>
														</button>
													</div>
												<?php } ?>
											</a>
										</li>
									<?php
										}
									?>
								</ul>
							</div>	
						</div>
					</div>

					<!-- All Posts Section -->
					<div class="container-fluid posts-wrapper" id="all-posts">
						<!-- All Posts Content -->
						<div class="container-fluid posts-body">
							<div class="container posts-title">
								<label>All Posts</label>
							</div>
							<!-- All Posts Section Posts-->
							<div class="container all-posts-wrapper">
								<ul class="list-group all-posts">
									<?php
                                        $userid = $_SESSION['userID'];
                                        $query = "SELECT * FROM posts WHERE community_name=?;";

                                        if (!mysqli_stmt_prepare($statement, $query)) {
                                            header("Location: index.php?error=sqlError");
                                            exit();
                                        }

                                        mysqli_stmt_bind_param($statement, "s", $cmtyName);
                                        mysqli_stmt_execute(($statement));

                                        $result = mysqli_stmt_get_result($statement);
										while($row = mysqli_fetch_array($result)) {
											$title = $row['title'];
											$type = -1;
											$cmtyID = $row['community_id'];
											$description = $row['descr'];
											$cmtyName = $row['community_name'];
											$username = $row['author'];
											$timeDiff = date('m/d/Y h:i:s a', time()) - date("H:i:s",strtotime($row['created_at']));
											$comments = $row['comments'];
											$postid = $row['id'];
						
											if (isset($_SESSION['userID'])) {
												$status_query = "SELECT count(*) as cntStatus,type FROM like_unlike WHERE userid=".$userid." and postid=".$postid;
												$status_result = mysqli_query($connection,$status_query);
												$status_row = mysqli_fetch_array($status_result);
												$count_status = $status_row['cntStatus'];

												if($count_status > 0){
													$type = $status_row['type'];
												}
							
												$like_query = "SELECT COUNT(*) AS cntLikes FROM like_unlike WHERE type=1 and postid=".$postid;
												$like_result = mysqli_query($connection,$like_query);
												$like_row = mysqli_fetch_array($like_result);
												$total_likes = $like_row['cntLikes'];
							
												$unlike_query = "SELECT COUNT(*) AS cntUnlikes FROM like_unlike WHERE type=0 and postid=".$postid;
												$unlike_result = mysqli_query($connection,$unlike_query);
												$unlike_row = mysqli_fetch_array($unlike_result);
												$total_unlikes = $unlike_row['cntUnlikes'];
											}
						
									?>
										<li class="list-group-item post-item">
											<a class="sticky-note" style="text-decoration: none; color: black;">
												<div class="post">
													<div class="post-title">
														<h5><?php echo $title?></h5>
													</div>
													<div class="poster-info">
														<small><?php echo $cmtyName.' • Post by '.$username?> • <?php if ($timeDiff < 1) { echo 'Just Now';} else {echo $timeDiff.' hour(s) ago';} ?></small> 
													</div>
												</div>
												<?php
														if (!isset($_SESSION['userID'])) {
															// get likes and dislikes for each post
															$sql = 'SELECT * FROM posts where community_name=?;';
															$statement = mysqli_stmt_init($connection);

															if (!mysqli_stmt_prepare($statement, $sql)) {
																header("Location: index.php?error=sqlError");
																exit();
															}
															else {
																mysqli_stmt_bind_param($statement, "s", $cmtyName);
																mysqli_stmt_execute(($statement));
											
																$results = mysqli_stmt_get_result($statement);
											
																if ($row = mysqli_fetch_assoc($results)) {
																	$likes = $row['likes'];
																	$dislikes = $row['dislikes'];
																	$comments = $row['comments'];
																}
																else {
																	header("Location: index.php?error=sqlError");
																	exit();
																}
															}
													?>
													<div class="interactions">
														<button tabindex="-1" class="bi bi-hand-thumbs-up interaction-btn" onclick="loginAlert()">
															<span class="like-count"><?php echo $likes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-hand-thumbs-down interaction-btn" onclick="loginAlert()">
																<span class="dislike-count"><?php echo $dislikes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-chat-left-text interaction-btn" onclick="loginAlert()">
															<span class="comment-count"><?php echo $comments?></span>
														</button>
													</div>
													<?php
														}
														else {
													?>
													<div class="interactions">
														<button tabindex="-1" class="bi bi-hand-thumbs-up interaction-btn like" id="like_<?php echo $postid; ?>">
															<span class="like-count" id="likes_<?php echo $postid; ?>"><?php echo $total_likes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-hand-thumbs-down interaction-btn unlike" id="unlike_<?php echo $postid; ?>">
																<span class="dislike-count" id="unlikes_<?php echo $postid; ?>"><?php echo $total_unlikes; ?></span>
														</button>
														<button tabindex="-1" class="bi bi-chat-left-text interaction-btn">
															<span class="comment-count"><?php echo ' '.$comments?></span>
														</button>
													</div>
												<?php } ?>
											</a>
										</li>
									<?php
										}
									?>
								</ul>

								<nav class="nav-pager container-fluid" aria-label="Page navigation example">
									<ul class="pagination justify-content-center container-fluid m-0 p-0">
										<li class="page-item">
											<a class="page-link" href="#" aria-label="Previous">
												<span aria-hidden="true">&laquo;</span>
												<span class="sr-only"></span>
											</a>
										</li>
										<li class="page-item">
											<a class="page-link" href="#">1</a>
										</li>
										<li class="page-item">
											<a class="page-link" href="#">2</a>
										</li>
										<li class="page-item">
											<a class="page-link" href="#">3</a>
										</li>
										<li class="page-item">
											<a class="page-link" href="#" aria-label="Next">
												<span aria-hidden="true">&raquo;</span>
												<span class="sr-only"></span>
											</a>
										</li>
									</ul>
								</nav>
							</div>	
						</div>
					</div>

					<?php
					}
					?>
				</div>

				<!-- Footer -->
				<div class="container footer-wrapper">
					<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">					
						<a href="/" class="col-md-4 d-flex align-items-center justify-content-center mb-3 link-dark text-decoration-none" id="title-img">
							<img src="assets/imgs/study.png" alt="" width="40">
						</a>
						<p class="col-md-4 mb-0" style="color: #00274C; text-align: center;">&copy; 2022 studySpot, Inc</p>
					</footer>
				</div>
			</div>
        </div>

	</body>
</html>