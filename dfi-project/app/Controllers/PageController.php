<?php namespace App\Controllers;
use App\Models\UserModel;

class PageController extends BaseController
{
    protected $UserModel;
    public function __construct()
    {
        $this->UserModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'DFI Project',
        ];
        return view('index', $data);
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard',
        ];
        return view('page/dashboard', $data);
    }

    public function profile()
    {
        $data = [
            'title' => 'My Profile'
        ];
        helper(['form']);
        $model = new UserModel();
        $data['user'] = $model->where('UserID', session()->get('UserID'))->first();
        return view('page/profile', $data);
    }

    public function update()
    {
        // $model = new UserModel();
        $oldUsername = $this->UserModel->getUser($this->request->getVar('UserUsername'));
        $data = [
            'title' => 'Edit Profile',
        ];
        helper(['form']);
      

        if($this->request->getMethod() == 'post')
        {
           
           // $usernameRule = 'required|is_unique[users.UserUsername]|min_length[4]|max_length[15]';
            if($oldUsername['UserUsername'] == $this->request->getVar('UserUsername'))
            {
                $usernameRule = 'required';
             //   return $usernameRule;
               // return $user;
            } 
            else
            {
                $usernameRule = 'required|is_unique[users.UserUsername]|min_length[4]|max_length[15]';
//return $usernameRule;
               // return false;
                // 'required|is_unique[users.UserUsername,UserID,{UserID}]|min_length[4]|max_length[15]'
            }
            
            // $id = session()->get('UserID');
            //validation
            $rules = [
                'UserName' => 'required',
                'UserEmail' => 'required|valid_email',
                'UserUsername' => $usernameRule,
                'UserHometown' => 'required',
                'UserBirthday' => 'required',
                'UserTwitter' => 'required',
                'UserInstagram' => 'required',
                'UserAva' => 'max_size[UserAva,1024]|is_image[UserAva]|mime_in[UserAva,image/jpg,image/jpeg,image/png]',
                'UserBio' => 'min_length[0]|max_length[100]',
            ];

            if($this->request->getPost('UserPassword') != '')
            {
				$rules['UserPassword'] = 'required|min_length[8]|max_length[255]';
				$rules['PasswordConfirm'] = 'matches[UserPassword]';
            }
            
            if(!$this->validate($rules))
            {
                $data['validation'] = $this->validator;
            }
            else
            {
                $avaFile = $this->request->getFile('UserAva');
                if($avaFile->getError() == 4)
                {
                   $avaName = session()->get('UserAva');
                    
                }
                else
                {
                    $avaName = $avaFile->getRandomName();
                    $avaFile->move('./images', $avaName);
                }
                
                //store the user in database
                //$model = new UserModel();
                $newData = [
                    'UserID' => session()->get('UserID'),
                    'UserName' => $this->request->getPost('UserName'),
                    'UserEmail' => $this->request->getPost('UserEmail'),
                    'UserUsername' => $this->request->getPost('UserUsername'),
                    'UserHometown' => $this->request->getPost('UserHometown'),
                    'UserBirthday' => $this->request->getPost('UserBirthday'),
                    'UserTwitter' => $this->request->getPost('UserTwitter'),
                    'UserInstagram' => $this->request->getPost('UserInstagram'),
                    'UserAva' => $avaName,
                    'UserBio' => $this->request->getPost('UserBio'),
                ];

                if($this->request->getPost('UserPassword') != '')
                {
                    $newData['UserPassword'] = $this->request->getPost('UserPassword');
                }
        
                $this->UserModel->save($newData);
                session()->setFlashdata('success', 'Successfuly Updated');
				return redirect()->to('/profile');
            }
        }

        $data['user'] = $this->UserModel->where('UserID', session()->get('UserID'))->first();
        return view('page/update', $data);
    }
}