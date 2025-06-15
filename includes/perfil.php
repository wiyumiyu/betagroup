<?php
?>

                <div class="col-md-6 col-sm-8 clearfix">

                    <ul class="user-info pull-left pull-none-xsm">

                        <!-- Profile Info -->
                        <li class="profile-info dropdown"><!-- add class "pull-right" if you want to place this from right -->

                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <?php 
                                $imagenperfil = "../assets/images/thumb.jpg";
//                                if ($_SESSION['imagen'] != "" && $_SESSION['imagen'] != "0"){
//                                       $imagenperfil = $_SESSION['imagen'];
//                                   
//                                }

                                
                                echo "<img src='" . $imagenperfil . "' alt='' class='img-circle' width='44' />";
                                echo $_SESSION['nombre'] ; 
                                
                                

                                ?>
                            </a>

<!--                            <ul class="dropdown-menu">-->

                                <!-- Reverse Caret -->
<!--                                <li class="caret"></li>-->

                                <!-- Profile sub-links -->
<!--                                <li>
                                    <a href="#">
                                        <i class="entypo-user"></i>
                                        Edit Profile
                                    </a>
                                </li>

                                <li>
                                    <a href="mailbox.html">
                                        <i class="entypo-mail"></i>
                                        Inbox
                                    </a>
                                </li>

                                <li>
                                    <a href="extra-calendar.html">
                                        <i class="entypo-calendar"></i>
                                        Calendar
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="entypo-clipboard"></i>
                                        Tasks
                                    </a>
                                </li>-->
<!--                            </ul>-->
                        </li>

                    </ul>

                    <ul class="user-info pull-left pull-right-xs pull-none-xsm">

                        <!-- Message Notifications -->

                        <!-- Task Notifications -->


                    </ul>

                </div>
