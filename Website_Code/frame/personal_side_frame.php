<aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li> <a class="waves-effect waves-dark" href="/personal/index.php" aria-expanded="false"><i class="mdi mdi-account-circle"></i><span class="hide-menu">个人中心</span></a>
                        </li>
                        <li> <a class="waves-effect waves-dark" href="/personal/info.php" aria-expanded="false"><i class="mdi mdi-account-edit"></i><span class="hide-menu">信息修改</span></a>
                        </li>
                        <li> <a class="waves-effect waves-dark" href="/personal/work.php" aria-expanded="false"><i class="mdi mdi-wunderlist"></i><span class="hide-menu">岗位信息</span></a>
                        </li>

                          <!--组员附加页-->
                            <?php if($person->work_info()["权限"]!=1) echo("<!--"); ?>
							<hr/>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/course.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">查课表录入</span></a>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/selfstudy.php" aria-expanded="false"><i class="mdi mdi-table-edit"></i><span class="hide-menu">查早表录入</span></a>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/ForOthers.php" aria-expanded="false"><i class="mdi mdi-account-switch"></i><span class="hide-menu">代查表</span></a>
                            <?php if($person->work_info()["权限"]!=1) echo("-->"); ?>
                            
                            <!--组长附加页-->
                            <?php if($person->work_info()["权限"]!=2) echo("<!--"); ?>
							<hr/>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_info.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">组员信息</span></a></li>
							<hr/>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work_selfstudy.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">组员查早数据</span></a></li>
							<li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work_courses.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">组员查课数据</span></a></li>
							<hr/>
							<li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work_selfstudy-lastWeek.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">上周组员查早数据</span></a></li>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work_courses-lastWeek.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">上周组员查课数据</span></a></li>
                            <?php if($person->work_info()["权限"]!=2) echo("-->"); ?>
                            
                            <!--队长附加页-->
                            <?php if($person->work_info()["权限"]!=3) echo("<!--"); ?>
							<hr/>
                            <li> <a class="waves-effect waves-dark" href="/personal/team_leader/all_member_info.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">队员信息</span></a></li>
							<hr/>
                            <li> <a class="waves-effect waves-dark" href="/personal/team_leader/all_member_work_selfstudy.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">所有组员查早数据</span></a></li>
							<li> <a class="waves-effect waves-dark" href="/personal/team_leader/all_member_work_courses.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">所有组员查课数据</span></a></li>
                            <?php if($person->work_info()["权限"]!=3) echo("-->"); ?>
						
							<!--数据组附加页-->
                            <?php if($person->work_info()["所属组"]!="数据组") echo("<!--"); ?>
							<hr/><h4>网页还没好的部分↓↓↓</h4>
                            <li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">早自习排班导入</span></a></li>
							<li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">队查课排班导入</span></a></li>
							<hr/>
							<li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">早自习数据导出</span></a></li>
							<li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">查课数据导出</span></a></li>
                            <?php if($person->work_info()["所属组"]!="数据组") echo("-->"); ?>
						
							<!--骨干附加页-->
                            <?php if($person->work_info()["权限"]<=1) echo("<!--"); ?>
							<hr/><h4>网页还没好的部分↓↓↓</h4>
                            <li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">老组员离岗</span></a></li>
							<li> <a class="waves-effect waves-dark" href="/personal/team_leader/#.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">新组员到岗</span></a></li>
                            <?php if($person->work_info()["权限"]<=1) echo("-->"); ?>
                    </ul>
                    <!-- <div class="text-center m-t-30">
                        <a href="#" class="btn waves-effect waves-light btn-warning hidden-md-down"> Upgrade to Pro</a>
                    </div> -->
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
            <!-- Bottom points-->
            <div class="sidebar-footer">
                <!-- item--><a href="#" class="link" data-toggle="tooltip" title="Settings"><i class="ti-settings"></i></a>
                <!-- item--><a href="#" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a>
                <!-- item--><a href="/log/logout.php" class="link" data-toggle="tooltip" title="Logout"><i class="mdi mdi-power"></i></a> </div>
            <!-- End Bottom points-->
        </aside>
