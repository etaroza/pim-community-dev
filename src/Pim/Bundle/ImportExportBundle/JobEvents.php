<?php

namespace Pim\Bundle\ImportExportBundle;

/**
 * Job events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobEvents
{
    /**
     * This event is thrown each a job profile is being edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EDIT_JOB_PROFILE     = 'pim_enrich.job_profile.pre_edit';

    /**
     * This event is thrown each a job profile has been edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EDIT_JOB_PROFILE    = 'pim_enrich.job_profile.post_edit';

    /**
     * This event is thrown each a job profile is being executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EXECUTE_JOB_PROFILE  = 'pim_enrich.job_profile.pre_execute';

    /**
     * This event is thrown each a job profile has been executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EXECUTE_JOB_PROFILE = 'pim_enrich.job_profile.post_execute';
}
