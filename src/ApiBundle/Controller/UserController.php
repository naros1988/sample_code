<?php

namespace ApiBundle\Controller;

class UserController extends ApiBaseController
{
    /**
     * Returns user data.
     *
     * @Route("/user/profile", name="user_profile", methods={"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns user profile data",
     *     @SWG\Schema(
     *         title="data",
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"ApiSingle"}))
     *     )
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     * )
     * @SWG\Tag(name="User")
     */
    public function profileAction()
    {
        $user = $this->getUser();

        return $this->serializedResponse($user, ['ApiSingle', "Avatar"]);
    }

    /**
     * Changing user password
     *
     * @Route("/user/change-password", name="user_change_password", methods={"PUT"})
     *
     * @SWG\Response(
     *     response=204,
     *     description="Password chnaged successfully"
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Form error",
     * ),
     * @SWG\Parameter(
     *         name="change_password",
     *         in="body",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="change_password", ref=@Model(type=ChangePasswordType::class))
     *        )
     * ),
     * )
     * @SWG\Tag(name="User")
     */
    public function changePasswordAction(ChangePasswordFormHandler $formHandler)
    {
        $formHandler->buildForm(
            ChangePasswordType::class,
            new ChangePasswordModel()
        );

        if ($formHandler->process() === true) {
            return $this->emptyView();
        }

        return $this->errorView($formHandler->getErrorsAsString());
    }

    /**
     * Sign tutorial as passed by user.
     *
     * @Route("/user/tutorial/complete", name="user_tutorial_complete", methods={"PUT"})
     *
     * @SWG\Response(
     *     response=204,
     *     description="Tutorial change to complete for user"
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="User not found",
     * )
     * @SWG\Tag(name="User")
     */
    public function tutorialCompletedAction(
        UserManagerInterface $userManager
    ) {
        $user = $this->getUser();

        $user->setTutorialCompleted(true);
        $userManager->persistAndFlush($user);

        return $this->emptyView();
    }

    /**
     * Adds user avatar.
     *
     * @Route("/user/profile", name="user_avatar", methods={"PUT"})
     * @SWG\Parameter(
     *         name="user_avatar",
     *         in="body",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="user_avatar", ref=@Model(type=UserAvatarType::class))
     *        )
     * ),
     * @SWG\Response(
     *     response=204,
     *     description="Avatar added to user",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * )
     * @SWG\Tag(name="User")
     */
    public function avatarAction(UserAvatarFormHandler $formHandler)
    {
        $formHandler->buildForm(
            UserAvatarType::class,
            new Avatar()
        );

        if (!$formHandler->process()) {
            return $this->errorView($formHandler->getErrorsAsString());
        }

        return $this->emptyView();
    }

    /**
     * Invites neighbours to application.
     *
     * @Route("/user/neighbor", name="user_neighbor", methods={"POST"})
     * @SWG\Parameter(
     *         name="neighbour_invitation",
     *         in="body",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="neighbour_invitation", ref=@Model(type=NeighbourInvitationType::class))
     *        )
     * ),
     * @SWG\Response(
     *     response=204,
     *     description="Invitations send",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * ),
     * @SWG\Parameter(
     *         name="content-apartment",
     *         in="header",
     *         type="integer",
     *         required=true
     * )
     * @SWG\Tag(name="User")
     */
    public function neighbourAction(CommunityRequestProvider $provider, NeighbourInvitationFormHandler $formHandler)
    {
        $neighbourInvitation = new NeighbourInvitation();
        $neighbourInvitation->setApartment($provider->getApartment());

        $formHandler->buildForm(NeighbourInvitationType::class, $neighbourInvitation);

        if (!$formHandler->process()) {
            return $this->errorView($formHandler->getErrorsAsString());
        }

        return $this->emptyView();
    }
}
