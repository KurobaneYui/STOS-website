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
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/course_up.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">查课表录入</span></a>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/selfstudy_up.php" aria-expanded="false"><i class="mdi mdi-table-edit"></i><span class="hide-menu">查早表录入</span></a>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_member/others.php" aria-expanded="false"><i class="mdi mdi-account-switch"></i><span class="hide-menu">代查表</span></a>
                            <?php if($person->work_info()["权限"]!=1) echo("-->"); ?>
                            
                            <!--组长附加页-->
                            <?php if($person->work_info()["权限"]<=1) echo("<!--"); ?>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_info.php" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">组员信息</span></a></li>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">组员提交数据</span></a></li>
                            <li> <a class="waves-effect waves-dark" href="/personal/group_leader/member_work-lastWeek.php" aria-expanded="false"><i class="mdi mdi-table-large"></i><span class="hide-menu">上周组员提交数据</span></a></li>
                            <?php if($person->work_info()["权限"]<=1) echo("-->"); ?>
                            
                            <!--队长附加页-->
                            <?php if($person->work_info()["权限"]!=3) echo("<!--"); ?>
                            <?php if($person->work_info()["权限"]!=3) echo("-->"); ?>
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
