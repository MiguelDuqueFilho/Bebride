<?php


use \BeBride\PageAdmin;

use \BeBride\Model;
use \BeBride\Page;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\EventGuest;
use \BeBride\Model\Deposition;

// use \BeBride\Model\Product;
// use \BeBride\Model\Category;
// use \BeBride\Model\Cart;
// use \BeBride\Model\Address; 
// use \Rain\Tpl\Exception;
// use \BeBride\Model\Order;
// use \BeBride\Model\OrderStatus;




$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index",[
		"notification"=>Model::getNotification(),
		"eventstype"=>Events::getEventsType(),
		"depositions"=>Deposition::getDepositionShow()
	]);


});


$app->get('/register', function() {
	
	$page = new Page();

	$page->setTpl("register", [
		"notification"=>Model::getNotification(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['person_firstname'=>'', 'person_lastname'=>'','person_email'=>'', 'person_phone'=>'']
	]);

});

$app->post("/register", function() {

	$user_password = (isset($_POST['user_password'])) ? $_POST['user_password'] : '';
	$_POST['user_password'] = '';  // esta linha é importante para a segurança da senha

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['person_firstname']) || $_POST['person_firstname'] == '')
	{
		User::setNotification("Preencha o seu nome.",'warning');
		header("location: /register");
		exit;	
	}
	
	if (!isset($_POST['person_lastname']) || $_POST['person_lastname'] == '')
	{
		User::setNotification("Preencha o nome completo.",'warning');
		header("location: /register");
		exit;	
	}

	if (!isset($_POST['person_email']) || $_POST['person_email'] == '')
	{
		User::setNotification("Preencha o seu e-mail.",'warning');
		header("location: /register");
		exit;	
	}

	if (!isset($_POST['person_phone']) || $_POST['person_phone'] == '')
	{
		User::setNotification("Preencha o seu telefone.",'warning');
		header("location: /register");
		exit;	
	}

	if ($user_password == '')
	{
		User::setNotification("Preencha a senha.",'warning');
		header("location: /register");
		exit;	
	}

	if (User::checkLoginExist($_POST['person_email']) === true) 
	{
		User::setNotification("Este endereço de e-mail já esta sendo usado por outro usuário.",'warning');
		header("location: /register");
		exit;	
	}

	
	$user = new User();

	$user->setValues([
		'user_type_id'=> (int) 2,
		'login_name'=>$_POST['person_email'],
		'password_hash'=>$user_password,
		'person_firstname'=>$_POST['person_firstname'],		
		'person_lastname'=>$_POST['person_lastname'],
		'person_email'=>$_POST['person_email'],
		'person_phone'=>$_POST['person_phone'],
		'person_whatsapp'=>'',
		'person_facebook'=>'',
		'person_instagram'=>'',
		'company_name'=>'',
		'person_jobrole'=>'',
		'person_about'=>'',
		'person_urlphoto'=>''

	]);


	$user->save();


	if ($user::login($_POST['person_email'],$user_password) === null)
	{
		User::setNotification("Login inexistente ou Senha Invalida.",'warning');
		header("location: /register");
		exit;
	}
	else 
	{
		header("location: /");
		exit;
	}


});


$app->get('/login', function() {

	$page = new Page();

	$page->setTpl("login",[
		"notification"=>Model::getNotification(),
		'loginValues'=>(isset($_SESSION['loginValues'])) ? $_SESSION['loginValues'] : ['person_email'=>'']
	]);

});

$app->post("/login", function() {

	try {

		if (User::login($_POST['login_name'], $_POST['user_password']) === null)
		{
			User::setNotification("Login inexistente ou Senha Invalida.",'warning');
			header("Location: /login");
			exit;
		}

	} catch(Exception $e) {

		User::setNotification($e->getMessage(),'error');
		header("Location: /login");
		exit;
	}
	
	if ((int)$_SESSION[User::SESSION]["user_type_id"] === 1 )
	{
		header("Location: /admin");
		exit;
	}
	if ((int)$_SESSION[User::SESSION]["user_type_id"] === 2 )
	{
		header("Location: /client");
		exit;
	}

	header("Location: /");
	exit;


});

$app->get("/logout", function() {

	User::logout();

	header("location: /");
	exit;
});



$app->get("/forgot/sent", function() {

	$page = new Page();

	$page->setTpl("forgot-sent",[
		"notification"=>Model::getNotification()
	]);

});



$app->get("/forgot/reset", function() {

	Model::clearNotification();

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	if ($user === null) {
		User::setNotification("Não foi possivel recuperar a senha.",'error');
		$page->setTpl("forgot-reset-success", array(
			"notification"=>Model::getNotification()
		));
	}
	else 
	{	
		$page->setTpl("forgot-reset", array(
			"notification"=>Model::getNotification(),
			"name"=>$user["person_firstname"],
			"code"=>$_GET["code"]
		));
	}
});


$app->post("/forgot/reset", function() {

	User::clearNotification();

	$forgot = User::validForgotDecrypt($_POST["code"]);

	if ($forgot ==null) {
		User::setNotification("Não foi possivel recuperar a senha.",'error');
	}
	else 
	{
		User::setForgotUsed($forgot["recovery_id"]);

		$user = new User();
	
		$user->getUser((int) $forgot["user_id"]);
	
		$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT, [
			"cost"=>12
		]);
	
		$user->setPassword($password_hash);
		User::setNotification("Senha alterada. faça o login com a nova senha",'success');
	}


	$page = new Page();
	
	$page->setTpl("forgot-reset-success",[
		"notification"=>Model::getNotification()
	]);
});

$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");
});


$app->post("/forgot", function() {

	User::clearNotification();

	$user = User::getForgot($_POST["login_name"], false);

	header("location: /forgot/sent");
	exit;

});

$app->post("/sendMail", function() {

	User::sendMailfromclient($_POST["client_name"],$_POST["client_email"],$_POST["message_email"]);

	header("location: /");
	exit;
});


$app->get("/invit/comfirm", function() {

	Model::clearNotification();

	$eventguestrecovery = EventGuest::validinvitDecrypt($_GET["code"]);

	$page = new Page();

	if ($eventguestrecovery === null) {
		Model::setNotification("Não foi possivel confirmar a presença..",'error');
		header("location: /");
	}
	else 
	{	
		$page->setTpl("invit-confirm", array(
			"notification"=>Model::getNotification(),
			"eventguest"=>$eventguestrecovery,
			"code"=>$_GET["code"]
		));
	}
	exit;

});


$app->post("/invit/comfirm", function() {

	Model::clearNotification();

	$eventguestrecovery = EventGuest::validinvitDecrypt($_POST["code"]);

	$eventguest_confirm_sel = $_POST["eventguest_confirm_sel"];

	EventGuest::setInvitConfirm( $eventguestrecovery["eventguest_id"], $eventguestrecovery["guestconfirm_id"], $eventguest_confirm_sel, $_SERVER["REMOTE_ADDR"]); 

	header("location: /");
	exit;

});


/* 


$app->get("/categories/:idcategory", function($idcategory) {

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination["pages"] ; $i++) { 
		array_push($pages, [
			'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category' => $category->getValues(),
		'products' => $pagination["data"],
		'pages' => $pages
	]);
});

$app->get("/products/:desurl", function($desurl) {

	$product = new Product();

	$product->getFromUrl($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);
});

$app->get("/cart", function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});


$app->get("/cart/:idproduct/add", function($idproduct) {

	$product = new Product();

	$product->get((int) $idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int) $_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}

	header("location: /cart");
	exit;
});


$app->get("/cart/:idproduct/minus", function($idproduct) {

	$product = new Product();

	$product->get((int) $idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("location: /cart");
	exit;
});



$app->get("/cart/:idproduct/remove", function($idproduct) {

	$product = new Product();

	$product->get((int) $idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product,true);

	header("location: /cart");
	exit;
});

$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['deszipcode']);

	header("location: /cart");
	exit;

});

$app->get("/checkout", function() {

	User::verifyLogin();


	$address = new Address();

	$cart = Cart::getFromSession();

	if (!isset($_GET['zipcode'])) {

		$_GET['zipcode'] = $cart->getdeszipcode();

	}
	if (isset($_GET['zipcode'])) 
	{
		$address->loadFromCep($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	};

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getzipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'checkoutError'=>Address::getMsgError()
	]);
	
});

$app->post("/checkout", function() {

	User::verifyLogin();

	Address::clearMsgError();
	
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') 
	{
		var_dump($_POST);
		exit;
		Address::setMsgError("Informe o Cep.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') 
	{
		Address::setMsgError("Informe o endereço.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['desnumber']) || $_POST['desnumber'] === '') 
	{
		Address::setMsgError("Informe o número do endereço.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') 
	{
		Address::setMsgError("Informe o bairro.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === '') 
	{
		Address::setMsgError("Informe o cidade.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') 
	{
		Address::setMsgError("Informe o estado.");
		header('location: /checkout');
		exit;
	}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') 
	{
		Address::setMsgError("Informe o país.");
		header('location: /checkout');
		exit;
	}



	$user = USER::getFromSession();

	$address = new Address();



	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setValues($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setValues([
		'idorder'=>(int)0,
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header("location: /order/".$order->getidorder());
	exit;


});


$app->get("/profile", function() {

	User::verifyLogin();

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

$app->post("/profile", function() {

	User::verifyLogin();

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '')
	{
		User::setError("Preencha o seu nome.");
		header("location: /profile");
		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '')
	{
		User::setError("Preencha o seu e-mail.");
		header("location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail()) 
	{
		if (User::checkLoginExist($_POST['desemail']) === true)
		{
			User::setError("Este e-mail já esta cadastrado.");
			header("location: /profile");
			exit;
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setValues($_POST);

	$user->save();

	var_dump($user->getValues());
	exit;
	User::setSuccess("Dados alterados com sucesso.");

	header("location: /profile");
	exit;

});

$app->get("/order/:idorder", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int) $idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);
});


$app->get("/boleto/:idorder", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int) $idorder);


	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "",$valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress();
	$dadosboleto["endereco2"] = $order->getdescity() ." - " . $order->getdesstate() . " -  CEP: " . $order->deszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR ;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");


	// include("include/funcoes_itau.php"); 
	// include("include/layout_itau.php");
});

$app->get("/profile/orders", function() {

	User::verifyLogin();

	$user = new User();

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getorders()
	]);

});

$app->get("/profile/orders/:idorder", function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int) $idorder);

	$cart = new Cart();

	$cart->get((int)$idorder );

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});

$app->get("/profile/change-password", function() {

	User::verifyLogin();

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);

});


$app->post("/profile/change-password", function() {

	User::verifyLogin();

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '' ) 
	{
		User::setError("Digite a senha atual.");
		header("location: /profile/change-password");
		exit;
	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '' ) 
	{
		User::setError("Digite a nova senha.");
		header("location: /profile/change-password");
		exit;
	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '' ) 
	{
		User::setError("Confirme a nova senha.");
		header("location: /profile/change-password");
		exit;
	}

	if ($_POST['current_pass'] === $_POST['new_pass']) 
	{
		User::setError("A sua nova senha deve ser diferente da atual.");
		header("location: /profile/change-password");
		exit;	
	}

	$user = User::getFromSession();

	if (!password_verify($_POST['current_pass'] , $user->getdespassword())) 
	{
		User::setError("A senha atual esta inválida.");
		header("location: /profile/change-password");
		exit;
	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setSuccess("Senha alterada com sucesso.");

	header("location: /profile/change-password");
	exit;
}); */

?>