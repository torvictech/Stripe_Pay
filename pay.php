<?php # pay.php
require('config.inc.php');
session_start();
?>
<!DOCTYPE html>
<!--[if lt IE 8 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 8)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <!--- Basic Page Needs
    ================================================== -->
    <meta charset="utf-8">
    <title>FlexTrac | Payment</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Mobile Specific Metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">

    <style type="text/css">

        .container {
            background: #fff url(images/patterns/grey.png);
            padding: 90px 0 102px 0;
        }
        .section-head h2 { font: 30px/42px montserrat-bold, sans-serif; }
        .desc { font: 14px/24px opensans-regular, sans-serif; }

    </style>

    <!--[if lt IE 9]>
        <script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!-- Favicons
     ================================================== -->
    <link rel="shortcut icon" href="../favicon.ico">
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>

<body>
    <!-- Header
    ================================================== -->
    <header id="top" class="static">

        <div class="row">

            <div class="col full">

                <div class="logo">
                    <a href="index.html"><img alt="" src="images/logo.png"></a>
                </div>

                <nav id="nav-wrap">

                    <a class="mobile-btn" href="#nav-wrap" title="Show navigation">Show navigation</a>
                    <a class="mobile-btn" href="#" title="Hide navigation">Hide navigation</a>

                    <ul id="nav" class="nav">
                        <li><a href="index.html">FlexTrac</a></li>
                        <li class="active"><a href="#">Payment</a></li>
                    </ul>

                </nav>

            </div>

        </div>

    </header> <!-- Header End -->
    <!-- Container
    ================================================== -->
    <section id="solutions">

        <div class="row section-head">
            <div class="col full">

				<?php 
				echo '<script type="text/javascript">Stripe.setPublishableKey("' . STRIPE_PUBLIC_KEY . '");</script>';
				
				// Check for a form submission:
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
					// Stores errors:
					$errors = array();
	
					// Need a payment token:
					if (isset($_POST['stripeToken'])) {
						$token = $_POST['stripeToken'];
		
						// Check for a duplicate submission, just in case:
						// Uses sessions, you could use a cookie instead.
						if (isset($_SESSION['token']) && ($_SESSION['token'] == $token)) {
							$errors['token'] = 'You are trying to resubmit the form - please retype the URL into your browser to proceed.';
						} else { // New submission.
							$_SESSION['token'] = $token;
						}		
		
					} else {
						$errors['token'] = 'The order cannot be processed - please make sure you have JavaScript enabled and try again.';
					}

					// Validate other form data!

					// If no errors, process the order:
					if (empty($errors)) {
		
						// create the charge on Stripe's servers - this will charge the user's card
						try {
			
							// Include the Stripe library:
							require_once('stripe-php-1.13.0/lib/Stripe.php');

							// set your secret key: remember to change this to your live secret key in production
							Stripe::setApiKey(STRIPE_PRIVATE_KEY);

							// Charge the order:
							$charge = Stripe_Charge::create(array(
								"amount" => (int)str_replace(".","",$_POST['pa']), // amount in cents
								"currency" => "usd",
								"card" => $token,
								"description" => $_POST['sn']
								)
							);

							// Check that it was paid:
							if ($charge->paid == true) {
				
								// Send the email to sales@flextrac.com
								// Celebrate!
				
							} else { // Charge was not paid!	
								echo '<div class="alert alert-error"><h4>Payment System Error!</h4>Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card.</div>';
							}
			
						} catch (Stripe_CardError $e) {
							// Card was declined.
							$e_json = $e->getJsonBody();
							$err = $e_json['error'];
							$errors['stripe'] = $err['message'];
						} catch (Stripe_ApiConnectionError $e) {
							// Network problem, perhaps try again.
						} catch (Stripe_InvalidRequestError $e) {
							// You screwed up in your programming. Shouldn't happen!
						} catch (Stripe_ApiError $e) {
							// Stripe's servers are down!
						} catch (Stripe_CardError $e) {
							// Something else that's not the customer's fault.
						}

					} // A user form submission error occurred, handled below.
	
				} // Form submission.
				?>

				<h2>Checkout.</h2>
				
				<span class="help-block">You can pay using:  Visa, Mastercard, American Express, JCB, Discover, and Diners Club.</span>

				<div align="right">
					<p class="lead">
						<script type="text/javascript">
							var currentDate = new Date()
							var day = currentDate.getDate()
							var month = currentDate.getMonth() + 1
							var year = currentDate.getFullYear()
							document.write("<b>" + day + "/" + month + "/" + year + "</b>")
						</script>
					</p>
				</div>
				
				<hr />

				<form action="pay.php" method="POST" id="payment-form">
					
					<label>Payment Amount</label>
					<span class="help-block">Use the following format - eg. 100.00</span>
					<input type="text" size="4" autocomplete="off" class="payment-amount input-mini" name="pa"/>
					
					<label>CTS Serial Number</label>
					<span class="help-block">7 digit unique number for your organization - eg. 1010000</span>
					<input type="text" size="20" autocomplete="off" class="serial-number input-medium" name="sn"/>

					<label>Credit Card Number</label>
					<span class="help-block">Enter the number without spaces or hyphens - eg. 4242424242424242</span>
					<input type="text" size="20" autocomplete="off" class="card-number input-medium"/>
					<label>CVC</label>
					<input type="text" size="4" autocomplete="off" class="card-cvc input-mini"/>
					<label>Expiration (MM/YYYY)</label>
					<div class="row">
						<div class="col one-fourth">
							<input type="text" size="2" class="card-expiry-month input-mini"/>
						</div>
						<div class="col one-fourth">
							<input type="text" size="4" class="card-expiry-year input-mini"/>
						</div>
					</div>

					<button type="submit" class="btn" id="submitBtn">Submit Payment</button>

					<span style="color:#D72828">
						<div id="payment-errors"></div>
						<?php // Show PHP errors, if they exist:
						if (isset($errors) && !empty($errors) && is_array($errors)) {
							echo '<div class="alert alert-error"><h4>Error!</h4>The following error(s) occurred:<ul>';
							foreach ($errors as $e) {
								echo "<li>$e</li>";
							}
							echo '</ul></div>';	
						}?>
					</span>

				</form>
				<script src="pay.js"></script>
		
				<a href="http://www.stripe.com" target="_blank"><img src="images/outline.png" /></a>

                <p class="lead">
                    FlexTrac Payments<br />
                    For Support: <a href="mailto:Support@FlexTrac.com">Click Here!</a> or Call Toll-Free: <a href="tel:+1.855.997.9933">+1.855.997.9933 ext.110</a><br />
                </p>

            </div>
        </div>

    </section> <!-- Container End -->

    <!-- footer
     ================================================== -->
    <footer>

        <div class="row">

            <div class="col g-7">
                <ul class="copyright">
                    <li>Copyright &copy; 2014 FlexTrac</li>
                    <li><a href="#" data-reveal-id="modal-privacy">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- modal-privacy -->
            <div id="modal-privacy" class="reveal-modal">

                <div class="description-box">
                    <h3>
                        FlexTrac Privacy Policy
                    </h3>

                    <p>
                        TORViC Technologies, Inc. (FlexTrac) values the privacy of those who visit our Web site, request demonstration systems and order our products. We think it is important for you to understand when and why we collect personally identifiable information and how we may use it. Please read the entire Privacy Policy before providing any personally identifiable information to us.
                    </p>

                    <h4>
                        Information Collection, Use and Sharing
                    </h4>

                    <p>
                        FlexTrac collects personally identifiable information, such as your name, address, telephone number, or e-mail address, when you provide this information to us voluntarily through this site, email or other direct contact with us. We will not sell or rent this information to anyone. Further, we will not disclose your personally identifiable information unless required to do so by law or in the good faith belief that such action is necessary to (1) conform to the edicts of the law, (2) protect and defend the rights or property of FlexTrac, or (3) as part of a transfer of assets to a successor in interest.

                        FlexTrac may use this information to send you marketing materials, and other information unless you notify us that you do not want to receive these materials.
                    </p>

                    <h4>
                        Hyperlinks
                    </h4>

                    <p>
                        While browsing the FlexTrac Web site, you may be able to access the Web sites of other organizations through a hyperlink. FlexTrac assumes no responsibility for the privacy practices of other organizations' Web sites and suggests you review the privacy statements/policies on such Web sites before sharing your personally identifiable data.
                    </p>

                    <h4>
                        Choice
                    </h4>

                    <p>
                        If you do not wish to receive information from FlexTrac, you may notify us by sending an e-mail to <a href="mailto:Info@FlexTrac.com">Info@FlexTrac.com</a> or by calling us at the telephone number provided at the top of this site. please provide us with your exact name and address as well as a description of the information you received. we will use reasonable efforts to refrain from including you when sending marketing materials by noting your election in our database.
                    </p>

                    <h4>
                        Access
                    </h4>

                    <p>
                        FlexTrac will permit you to access information about you in our database by contacting <a href="mailto:Info@FlexTrac.com">Info@FlexTrac.com</a>. If you believe any of the information is incorrect or needs updating, please advise us. We will correct our records upon verification of the requested change.
                    </p>

                    <h4>
                        Security
                    </h4>

                    <p>
                        FlexTrac will take reasonable and prudent precautions to ensure that your personally identifiable data is protected against unauthorized access, use, or disclosure.
                    </p>

                    <h4>
                        Enforcement
                    </h4>

                    <p>If you believe for any reason that FlexTrac is not abiding by this privacy policy, please contact us at <a href="mailto:Info@FlexTrac.com">Info@FlexTrac.com</a>, and FlexTrac will investigate, correct as appropriate, and advise you of the correction. Please identify the issue as a Privacy Policy concern in your communication to FlexTrac.</p>

                </div>

                <div class="link-box">
                    <a class="close-reveal-modal">Close</a>
                </div>

            </div><!-- modal-privacy End -->

            <div class="col g-5 pull-right">
                <ul class="social-links">
                    <li><a href="#"><i class="icon-facebook"></i></a></li>
                    <li><a href="#"><i class="icon-twitter"></i></a></li>
                    <li><a href="#"><i class="icon-google-plus-sign"></i></a></li>
                    <li><a href="#"><i class="icon-linkedin"></i></a></li>
                    <li><a href="#"><i class="icon-skype"></i></a></li>
                    <li><a href="#"><i class="icon-rss-sign"></i></a></li>
                </ul>
            </div>

        </div>

    </footer> <!-- Footer End-->
    <!-- Java Script
    ================================================== -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.10.2.min.js"><\/script>')</script>
    <script type="text/javascript" src="js/jquery-migrate-1.2.1.min.js"></script>
    <script src="js/smoothscrolling.js"></script>
    <script src="js/jquery.reveal.js"></script>
    
</body>

</html>